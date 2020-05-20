{**
 *      Lathanao Modules
 *
 *      @author         Lathanao <welcome@lathanao.com>
 *      @copyright      2019 Lathanao
 *      @license        OSL-3
 *      @version        1.2.0
 *}
<div  id="modalgift"
      quickview-modal-{$product->id|escape:'htmlall':'UTF-8'}-
      {if isset($product->id_product_attribute)}
        {$product->id_product_attribute|escape:'htmlall':'UTF-8'}
      {else}
        0
      {/if}"
      class="modal fade quickview"
      tabindex="-1"
      role="dialog"
      aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-6 col-sm-6 hidden-xs-down">

            {block name='product_cover_thumbnails'}
              {include file='catalog/_partials/product-cover-thumbnails.tpl'}
            {/block}
            <div class="arrows js-arrows">
              <i class="material-icons arrow-up js-arrow-up">&#xE316;</i>
              <i class="material-icons arrow-down js-arrow-down">&#xE313;</i>
            </div>

          </div>
          <div class="col-md-6 col-sm-6">
            <h1 class="h1">{$product->name|escape:'htmlall':'UTF-8'}</h1>

          {block name='product_description_short'}
            <div id="product-description-short" itemprop="description">{$product.description_short nofilter}</div>
          {/block}

          {block name='product_description'}
            <div id="product-description-short" itemprop="description">{$product.description nofilter}</div>
          {/block}

          </div>
        </div>
      </div>

      <div class="modal-footer">
          {block name='product_buy'}
            <div class="product-actions">
              <form action="{$urlCartAddRule|escape:'htmlall':'UTF-8'}" method="post">
                <input type="hidden" name="addDiscount" value="{$addDiscount|escape:'htmlall':'UTF-8'}">
                <input type="hidden" name="discount_name" value="{$discount_name|escape:'htmlall':'UTF-8'}">
                <input type="hidden" name="action" value="show">
                <button type="text" class="btn btn-secondary" data-dismiss="modal">
                    {$setup_aogift['AO_GIFT_BTN_2']|escape:'htmlall':'UTF-8'}
                </button>
                <button type="submit" class="btn btn-primary">{$setup_aogift['AO_GIFT_BTN_1']|escape:'htmlall':'UTF-8'}</button>
              </form>
            </div>
          {/block}
      </div>
    </div>
  </div>
</div>
