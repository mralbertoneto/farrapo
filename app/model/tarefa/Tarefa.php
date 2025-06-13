<?php
/**
 * Tarefa Active Record
 */
class Tarefa extends TRecord
{
    const TABLENAME  = 'tarefas';
    const PRIMARYKEY = 'id';
    const IDPOLICY   =  'max'; // ou 'serial' se for autoincremento no banco

    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('titulo');
        parent::addAttribute('descricao');
        parent::addAttribute('usuario_id');
        parent::addAttribute('tipo');
        parent::addAttribute('data_prevista');
        parent::addAttribute('data_entrega');
        parent::addAttribute('status');
    }

    /**
     * Retorna o nome do usuário responsável
     */
    public function get_usuario_nome()
    {
        $usuario = new SystemUsers($this->usuario_id);
        return $usuario->name;
    }

    /**
     * Formata a data prevista (opcional)
     */
    public function get_data_prevista_br()
    {
        return TDate::date2br($this->data_prevista);
    }

    /**
     * Formata a data de entrega (opcional)
     */
    public function get_data_entrega_br()
    {
        return TDate::date2br($this->data_entrega);
    }
}
