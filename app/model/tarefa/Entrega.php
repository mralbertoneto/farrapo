<?php
class Entrega extends TRecord
{
    const TABLENAME  = 'entregas';
    const PRIMARYKEY = 'id';
    const IDPOLICY   = 'serial';

    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('tipo_entrega');
        parent::addAttribute('data_limite');
        parent::addAttribute('data_entregue');
        parent::addAttribute('status');
        parent::addAttribute('observacoes');
    }

    public function get_data_limite_br()
    {
        return TDate::date2br($this->data_limite);
    }

    public function get_data_entregue_br()
    {
        return TDate::date2br($this->data_entregue);
    }
}
