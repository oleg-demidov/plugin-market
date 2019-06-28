<?php
/*
 * LiveStreet CMS
 * Copyright © 2013 OOO "ЛС-СОФТ"
 *
 * ------------------------------------------------------
 *
 * Official site: www.livestreetcms.com
 * Contact e-mail: office@livestreetcms.com
 *
 * GNU General Public License, version 2:
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *
 * ------------------------------------------------------
 *
 * @link http://www.livestreetcms.com
 * @copyright 2013 OOO "ЛС-СОФТ"
 * @author Oleg Demidov 
 *
 */

/**
 * Экшен обработки ajax запросов
 * Ответ отдает в JSON фомате
 *
 * @package actions
 * @since 1.0
 */
class PluginMarket_ActionPayment extends ActionPlugin{
    
    public $oUserProfile = null;
    
    public $iCountPaid = 0;

    public $iCountNotPaid = 0;


    public function Init()
    {
        if(!$this->oUserProfile and !$this->oUserProfile = $this->User_GetUserCurrent()){
            return $this->EventNotFound();
        }
    }
    protected function RegisterEvent() {
        
        $this->RegisterEventExternal('Trash', 'PluginMarket_ActionPayment_EventTrash');
        $this->AddEventPreg( '/^trash$/i', '/^(paid|not_paid)?$/i', '/^(page([\d]+))?$/i', ['Trash::EventProducts', 'trash']);
        $this->AddEventPreg( '/^trash$/i', '/^remove-product$/i', ['Trash::EventRemoveProduct', 'trash']);
        $this->AddEventPreg( '/^choose-provider$/i', 'EventChooseProvider');
    }
    
     
    
    public function EventBillPaid($oBill) {
        $this->Viewer_Assign('oBill', $oBill);
        $this->SetTemplateAction('bill-paid');
    }
    
    public function EventChooseProvider(){
        $oUserProfile = $this->User_GetUserCurrent();
        
        $aBillsIds = getRequest('bills')?getRequest('bills'):[0];
                
        if(!$aBills = $this->PluginMarket_Payment_GetBillItemsByFilter(['id in' => $aBillsIds])){
            $this->Message_AddError($this->Lang_Get('plugin.payment.notice.error_choose_bill'), null, true);
            Router::LocationAction('payment/'.$oUserProfile->getLogin().'/bills');
        }
        
        $oPayment = Engine::GetEntity('PluginMarket_Payment_Payment');
        $oPayment->setBills($aBills);
        
        if(!$oPayment->_Validate()){
            $this->Message_AddError($oPayment->_getValidateError(), null, true);
            Router::LocationAction('payment/'.$oUserProfile->getLogin().'/bills');
        }
        
        if(!$oPayment->Save()){
            $this->Message_AddError($this->Lang_Get('common.error'), null, true);
            Router::LocationAction('payment/'.$oUserProfile->getLogin().'/bills');
        }
        
        $this->Viewer_Assign('oPayment', $oPayment);
        $this->SetTemplateAction('choose-provider');        
        
    }
    
    public function EventShutdown() {
        $this->Viewer_Assign('iCountPaid', $this->iCountPaid);
        $this->Viewer_Assign('iCountNotPaid', $this->iCountNotPaid);
        $this->Viewer_Assign('oUserProfile', $this->oUserProfile);
    }
}