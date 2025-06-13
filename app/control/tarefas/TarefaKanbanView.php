<?php
/**
 * TarefaKanbanView
 * Exibe as tarefas em um quadro Kanban baseado no campo "status"
 */
class TarefaKanbanView extends TPage
{
    private $kanban;

    public function __construct()
    {
        parent::__construct();

        // Definição fixa das colunas (status)
        $statuses = [
            'Pendente'    => 'Pendente',
            'Entregue'    => 'Entregue',
            'Revisão'  => 'Revisão',
            'Aprovado'    => 'Aprovado'
        ];

        // Cria o componente Kanban
        $this->kanban = new TKanban;
        $this->kanban->setStageHeight('70vh');

        // Adiciona cada coluna (stage)
        foreach ($statuses as $id => $title) {
            $this->kanban->addStage($id, $title, (object)['status'=>$id]);
        }

        // Carrega as tarefas
        TTransaction::open('sample');
        $tarefas = Tarefa::all();
        TTransaction::close();

        // Adiciona cada tarefa como card
        foreach ($tarefas as $tarefa) {
            $id      = $tarefa->id;
            $stage   = $tarefa->status;
            $title   = $tarefa->titulo;
            
            TTransaction::open('permission');
            $userLocalize = new SystemUser($tarefa->usuario_id);
            TTransaction::close();

            $userView = $userLocalize->name;
            $userViewFormat = "Responsável: "."<span style=\"color:red;\">{$userView}</span>";
            $desc  = $tarefa->descricao ?? ''; // garante string não nula
            $content = nl2br(substr($desc, 0, 100));

            // $content  = "<strong>Responsável:</strong> {$userName}<br>{$snippet}";

            $this->kanban->addItem($id, $stage, $title, $userViewFormat, $content, NULL, $tarefa);
        }

        // Ações nos cards
        $this->kanban->addItemAction('Editar', new TAction(['TarefaForm','onEdit']), 'far:edit blue');
        // $this->kanban->addItemAction('Excluir', new TAction([__CLASS__,'onDelete']), 'far:trash-alt red');

        // Atualiza status ao mover carta
        $this->kanban->setItemDropAction(new TAction([__CLASS__,'onUpdateItemDrop']));

        parent::add($this->kanban);
    }

    /**
     * Atualiza o status da tarefa após drop
     */
    public static function onUpdateItemDrop($param)
    {
        if (empty($param['order'])) {
            return;
        }
        try {
            TTransaction::open('sample');
            foreach ($param['order'] as $sequence => $id) {
                $tarefa = new Tarefa($id);
                $tarefa->status = $param['stage_id'];
                $tarefa->store();
            }
            TTransaction::close();
        }
        catch (Exception $e) {
            // new TMessage('error', \$e->getMessage());
            TTransaction::rollback();
        }
    }

    /**
     * Exclui a tarefa e recarrega a visão
     */
    public static function onDelete($param)
    {
        // Confirma exclusão
        // if (empty(\$param['delete']) || \$param['delete'] != 1) {
            // \$action = new TAction([__CLASS__,'onDelete'], ['delete'=>1, 'key'=>\$param['key']]);
            // new TQuestion('Deseja realmente excluir esta tarefa?', \$action);
            // return;
        // }
        // try {
        //     TTransaction::open('sample');
        //     \$tarefa = new Tarefa(\$param['key']);
        //     \$tarefa->delete();
        //     TTransaction::close();
        //     // recarrega a página
        //     AdiantiCoreApplication::loadPage(__CLASS__, 'onReload');
        // }
        // catch (Exception \$e) {
        //     new TMessage('error', \$e->getMessage());
        //     TTransaction::rollback();
        // }
    }

    /**
     * Drop de colunas (reordena não implementado)
     */
    public static function onReload(){}
}
