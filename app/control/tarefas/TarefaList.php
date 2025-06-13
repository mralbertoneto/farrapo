<?php

class TarefaList extends TPage
{
    private $form;
    private $datagrid;
    private $pageNavigation;
    private $loaded;
    private $filter_criteria;
    private static $database = 'sample';
    private static $activeRecord = 'Tarefa';
    private static $primaryKey = 'id';
    private static $formName = 'form_TarefaList';
    private $showMethods = ['onReload', 'onSearch', 'onRefresh', 'onClearFilters'];
    private $limit = 20;

    public function __construct($param = null)
    {
        parent::__construct();

        if (!empty($param['target_container'])) {
            $this->adianti_target_container = $param['target_container'];
        }

        $this->form = new BootstrapFormBuilder(self::$formName);
        $this->form->setFormTitle("Listagem de Tarefas");

        $id     = new TEntry('id');
        $titulo = new TEntry('titulo');
        $tipo   = new TCombo('tipo');
        $status = new TCombo('status');

        $tipo->addItems(['Texto' => 'Texto', 'Slide' => 'Slide', 'Revisão' => 'Revisão', 'Unificação' => 'Unificação']);
        $status->addItems(['Pendente' => 'Pendente', 'Entregue' => 'Entregue', 'Em revisão' => 'Em revisão', 'Aprovado' => 'Aprovado']);

        $id->setSize('100%');
        $titulo->setSize('100%');
        $tipo->setSize('100%');
        $status->setSize('100%');

        $this->form->addFields([new TLabel("ID")], [$id], [new TLabel("Título")], [$titulo]);
        $this->form->addFields([new TLabel("Tipo")], [$tipo], [new TLabel("Status")], [$status]);

        $this->form->setData(TSession::getValue(__CLASS__ . '_filter_data'));

        $this->form->addAction("Buscar", new TAction([$this, 'onSearch']), 'fas:search #ffffff')->addStyleClass('btn-primary');
        $this->form->addAction("Cadastrar", new TAction(['TarefaForm', 'onEdit']), 'fas:plus #69aa46');

        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->filter_criteria = new TCriteria;

        $this->datagrid->style = 'width: 100%';
        $this->datagrid->setHeight(250);

        $column_id            = new TDataGridColumn('id', "ID", 'center', '50px');
        $column_titulo        = new TDataGridColumn('titulo', "Título", 'left');
        $column_tipo          = new TDataGridColumn('tipo', "Tipo", 'center');
        $column_user          = new TDataGridColumn('usuario_id', "Usuário", 'center');
        $column_data_prevista = new TDataGridColumn('data_prevista', "Data Prevista", 'center');
        $column_data_entrega  = new TDataGridColumn('data_entrega', "Data Entrega", 'center');
        $column_status        = new TDataGridColumn('status', "Status", 'center');

        $column_data_prevista->setTransformer(function($value, $object, $row) 
        {
            if(!empty(trim($value)))
            {
                try
                {
                    $date = new DateTime($value);
                    return $date->format('d/m/Y');
                }
                catch (Exception $e)
                {
                    return $value;
                }
            }
        });

        $column_data_entrega->setTransformer(function($value, $object, $row) 
        {
            if(!empty(trim($value)))
            {
                try
                {
                    $date = new DateTime($value);
                    return $date->format('d/m/Y');
                }
                catch (Exception $e)
                {
                    return $value;
                }
            }
        });

        $column_user->setTransformer(function($value, $object, $row) 
        {

            try{
                
                TTransaction::open('permission');
                    $user = new SystemUser($value);
                    $userview = $user->name;
                
                TTransaction::close();  

                return $userview;

            }
            catch (Exception $e)
            {
                return $value;
            }
            
           

        });

        $column_status->setTransformer(function($value, $object, $row, $cell = null, $last_row = null)
        {

// $status->addItems(['Pendente' => 'Pendente', 'Entregue' => 'Entregue', 'Em revisão' => 'Em revisão', 'Aprovado' => 'Aprovado']);    

            if($value == 'Aprovado')
            {
                return '<span class="label label-success">Aprovado</span>';
            }
            elseif($value == 'Pendente')
            {
                return '<span class="label label-danger">Pendente</span>';
            }
            elseif($value == 'Entregue')
            {
                return '<span class="label label-warning">Entregue</span>';
            }
            elseif($value == 'Revisão')
            {
                return '<span class="label label-primary">Revisão</span>';
            }

           
        });

        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_titulo);
        $this->datagrid->addColumn($column_user);
        $this->datagrid->addColumn($column_tipo);
        $this->datagrid->addColumn($column_data_prevista);
        $this->datagrid->addColumn($column_data_entrega);
        $this->datagrid->addColumn($column_status);

                $action_edit = new TDataGridAction(['TarefaForm', 'onEdit'], ['id' => '{id}']);
        $action_edit->setLabel("Editar");
        $action_edit->setImage('far:edit #478fca');

        $action_delete = new TDataGridAction([$this, 'onDelete'], ['id' => '{id}']);
        $action_delete->setLabel("Excluir");
        $action_delete->setImage('fas:trash-alt #dd5a43');

        $this->datagrid->addAction($action_edit);
        $this->datagrid->addAction($action_delete);

        $this->datagrid->createModel();

        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());

        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add($this->form);
        $container->add($this->datagrid);
        $container->add($this->pageNavigation);

        parent::add($container);
    }

    public function onSearch($param = null)
    {
        $data = $this->form->getData();

        $filters = [];
        if (!empty($data->id)) {
            $filters[] = new TFilter('id', '=', $data->id);
        }
        if (!empty($data->titulo)) {
            $filters[] = new TFilter('titulo', 'like', "%{$data->titulo}%");
        }
        if (!empty($data->tipo)) {
            $filters[] = new TFilter('tipo', '=', $data->tipo);
        }
        if (!empty($data->status)) {
            $filters[] = new TFilter('status', '=', $data->status);
        }

        TSession::setValue(__CLASS__ . '_filters', $filters);
        TSession::setValue(__CLASS__ . '_filter_data', $data);

        $this->form->setData($data);

        $this->onReload(['offset' => 0, 'first_page' => 1]);
    }

    public function onReload($param = NULL)
    {
        try {
            TTransaction::open(self::$database);
            $repository = new TRepository(self::$activeRecord);
            $criteria = new TCriteria;
            $criteria->setProperties($param);
            $criteria->setProperty('limit', $this->limit);

            if ($filters = TSession::getValue(__CLASS__ . '_filters')) {
                foreach ($filters as $filter) {
                    $criteria->add($filter);
                }
            }

            $objects = $repository->load($criteria, FALSE);
            $this->datagrid->clear();

            if ($objects) {
                foreach ($objects as $object) {
                    $this->datagrid->addItem($object);
                }
            }

            $criteria->resetProperties();
            $count = $repository->count($criteria);
            $this->pageNavigation->setCount($count);
            $this->pageNavigation->setProperties($param);
            $this->pageNavigation->setLimit($this->limit);

            TTransaction::close();
            $this->loaded = true;

            return $objects;
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }

    public function onDelete($param = null)
    {
        if (isset($param['delete']) && $param['delete'] == 1) {
            try {
                $key = $param['key'];
                TTransaction::open(self::$database);
                $object = new Tarefa($key, FALSE);
                $object->delete();
                TTransaction::close();
                $this->onReload($param);
                new TMessage('info', AdiantiCoreTranslator::translate('Record deleted'));
            } catch (Exception $e) {
                new TMessage('error', $e->getMessage());
                TTransaction::rollback();
            }
        } else {
            $action = new TAction([$this, 'onDelete']);
            $action->setParameters($param);
            $action->setParameter('delete', 1);
            new TQuestion(AdiantiCoreTranslator::translate('Do you really want to delete ?'), $action);
        }
    }

    public function onShow($param = null) {}

    public function show()
    {
        if (!$this->loaded && (!isset($_GET['method']) || !in_array($_GET['method'], $this->showMethods))) {
            $this->onReload();
        }
        parent::show();
    }
}
