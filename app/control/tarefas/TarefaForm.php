<?php
/**
 * TarefaForm
 * Formulário de cadastro/edição de tarefas
 */
class TarefaForm extends TPage
{
    protected $form;
    private $formFields = [];
    private static $database = 'sample';
    private static $activeRecord = 'Tarefa';
    private static $primaryKey = 'id';
    private static $formName = 'form_Tarefa';

    public function __construct()
    {
        parent::__construct();

        $this->form = new BootstrapFormBuilder('form_Tarefa');
        $this->form->setFormTitle('Cadastro de Tarefas');

        $id           = new TEntry('id');
        $titulo       = new TEntry('titulo');
        $descricao    = new TText('descricao');
        // $usuario_id   = new TEntry('usuario_id');
        $usuario_id   = new TDBCombo('usuario_id', 'permission', 'SystemUser', 'id', 'name');
        $tipo         = new TCombo('tipo');
        $data_prevista= new TDate('data_prevista');
        $data_entrega = new TDate('data_entrega');
        $status       = new TCombo('status');

        $id->setEditable(FALSE);

        $tipo->addItems(['Texto' => 'Texto', 'Slide' => 'Slide', 'Revisão' => 'Revisão', 'Unificação' => 'Unificação']);
        $status->addItems(['Pendente' => 'Pendente', 'Entregue' => 'Entregue', 'Revisão' => 'Revisão', 'Aprovado' => 'Aprovado']);

        $this->form->addFields([new TLabel('ID')], [$id]);
        $this->form->addFields([new TLabel('Título')], [$titulo]);
        $this->form->addFields([new TLabel('Descrição')], [$descricao]);
        $this->form->addFields([new TLabel('Usuário Responsável')], [$usuario_id]);
        $this->form->addFields([new TLabel('Tipo')], [$tipo]);
        $this->form->addFields([new TLabel('Data Prevista')], [$data_prevista]);
        $this->form->addFields([new TLabel('Data Entrega')], [$data_entrega]);
        $this->form->addFields([new TLabel('Status')], [$status]);

        $this->form->addAction('Salvar', new TAction([$this, 'onSave']), 'fa:save green');
        $this->form->addAction('Voltar', new TAction(['TarefaList', 'onReload']), 'fa:arrow-left red');

        parent::add($this->form);
    }

    public function onSave($param)
    {
        try {
            TTransaction::open(self::$database);

            $data = $this->form->getData();
            $this->form->validate();

            $object = new Tarefa;
            $object->fromArray((array) $data);
            $object->store();

            $this->form->setData($object);

            TTransaction::close();

            new TMessage('info', 'Tarefa salva com sucesso');
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }

    public function onEdit( $param )
    {
        try
        {
            if (isset($param['key']))
            {
                $key = $param['key'];  // get the parameter $key
                TTransaction::open(self::$database); // open a transaction

                $object = new Tarefa($key); // instantiates the Active Record 

                $this->form->setData($object); // fill the form 

                TTransaction::close(); // close the transaction 
            }
            else
            {
                $this->form->clear();
            }
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }

    /**
     * Clear form data
     * @param $param Request
     */
    public function onClear( $param )
    {
        $this->form->clear(true);

    }

    public function onShow($param = null)
    {

    }




}