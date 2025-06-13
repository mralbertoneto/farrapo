<?php
class Revisao extends TRecord
{
    const TABLENAME  = 'revisoes';
    const PRIMARYKEY = 'id';
    const IDPOLICY   = 'serial';

    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('etapa');
        parent::addAttribute('responsavel_id');
        parent::addAttribute('data_prevista');
        parent::addAttribute('data_conclusao');
        parent::addAttribute('status');
        parent::addAttribute('observacoes');
    }

    public function get_responsavel_nome()
    {
        $usuario = new SystemUsers($this->responsavel_id);
        return $usuario->name;
    }

    public function get_data_prevista_br()
    {
        return TDate::date2br($this->data_prevista);
    }

    public function get_data_conclusao_br()
    {
        return TDate::date2br($this->data_conclusao);
    }
}
