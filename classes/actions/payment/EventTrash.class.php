<?php

/**
 * Description of ActionModeration_EventModeration
 *
 * @author oleg
 */
class PluginMarket_ActionPayment_EventTrash extends Event {
    
   
    public function Init() {
        $this->iCountPaid = $this->PluginMarket_Payment_GetCountFromProductByFilter([
            '#where' => [
                't.date_payment IS NOT NULL AND 1=?d' => [1]
            ]
        ]);
        $this->iCountNotPaid = $this->PluginMarket_Payment_GetCountFromProductByFilter(['date_payment' => null]);
        
        $this->Menu_Get('profile')->setActiveItem('trash');
    }
    
    public function EventProducts($oUserProfile = null) {
        $sState = $this->GetParam(0, 'not_paid');
        
        $iPage = $this->GetParamEventMatch(1,2)?$this->GetParamEventMatch(1,2):1;
        
        $aFilter = [
            'user_id' => $this->oUserProfile->getId(),
            '#page' => [$iPage, Config::Get('plugin.payment.products.per_page')],
            '#order' => ['date_create' => 'desc']
        ];
        
        if($sState == 'no_paid'){
            $aFilter["paid"] = 0;
        }elseif($sState == 'paid'){
            $aFilter["paid"] = 1;
        }
        
        $aProducts = $this->PluginMarket_Payment_GetProductItemsByFilter($aFilter);
        
        $aPaging = $this->Viewer_MakePaging(
            $aProducts['count'], 
            $iPage, 
            Config::Get('plugin.payment.products.per_page'), 
            Config::Get('plugin.payment.products.view_page_count'), 
            Router::GetPath('payment/'.$this->oUserProfile->getLogin().'/trash/'.$sState)
        ); 
        
        $this->SetTemplateAction('trash');
        
        $this->Viewer_Assign('aProducts', $aProducts['collection']);
        $this->Viewer_Assign('aPaging', $aPaging);
        $this->Viewer_Assign('sState', $sState);
        $this->Viewer_Assign('oUserProfile', $oUserProfile);
    }
      
    
    public function EventDeleteProduct()
    {
        /*
         * Получение сущности по параметрам из реквеста
         */
        $oProduct = $this->PluginModeration_Moderation_GetProductById(getRequest('id'));
        
        if($oProduct->getUserId() != $this->oUserProfile->getId()){
            $this->Message_AddError($this->Lang_Get('plugin.payment.notices.trash.error_not_author_product'));
            
        }else{
            /*
            * Удаление
            */
            if($oProduct->Delete()){
                $this->Message_AddNotice($this->Lang_Get('common.success.remove'));
            }
        }
        
                
        return $this->EventProducts();
    }
    
    
}
