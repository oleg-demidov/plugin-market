{component_define_params params = ['name', 'oProduct', 'classes', 'attributes']}

{block 'product_options'}
    
{/block}
<div>
    <div class="row mt-2">
        <div class="col-md-1">{$oProduct->getId()}</div>
        <div class="col-md-1">{component "bs-form.checkbox" name="{$name|default:'products'}[]" value=$oProduct->getId()}</div>
        <div class="col-md-3">{$oProduct->getDescription()}</div>
        <div class="col-md-3">{$oProduct->getDateCreate()}</div>
        <div class="col-md-2">{$oProduct->getPrice()} {lang "plugin.payment.currency.{$oProduct->getCurrency()}"}</div>
        <div class="col-md-2">
            {component "bs-button" 
                url   = {router page="payment/process/?products[]={$oProduct->getId()}"}
                text  = $aLang.plugin.payment.trash.pay
                bmods = "outline-success sm" }
            {component "bs-button" 
                url   = {router page="payment/trash/remove-product/?id={$oProduct->getId()}"}
                icon  = [icon => 'times', display => 's']
                bmods = "outline-danger sm" }
        </div>
    </div>
        <hr class="m-0 mt-2">
</div>