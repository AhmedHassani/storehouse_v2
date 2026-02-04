<div class="modal-header p-2">
    <h4 class="modal-title product-title"></h4>
    <button class="close call-when-done" type="button" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<div id="add-to-cart-form">
    <div class="modal-body">
        <div class="media flex-wrap gap-3">
            <div class="box-120 rounded border">
                <img class="img-fit rounded"
                     src="{{$product['image_fullpath'][0]}}"
                     data-zoom="{{$product['image_fullpath'][0]}}"
                     alt="{{ translate('Product image') }}">
                <div class="cz-image-zoom-pane"></div>
            </div>

            <div class="details media-body">
                <h5 class="product-name"><a href="#"
                                            class="h3 mb-2 product-title">{{ Str::limit($product->name, 100) }}</a>
                </h5>

                <div class="mb-2">
                    @if($product->discount > 0)
                        <span class="h3 font-weight-normal text-decoration-line-through">
                    {{ Helpers::set_symbol($product['price']) }}
                </span>
                    @endif
                    <span class="h2">
                    {{ Helpers::set_symbol(($product['price']- Helpers::discount_calculate($product, $product['price']))) }}
                </span>
                </div>
                <div class="mb-0 text-dark">
                    <span
                        class="stock-badge">{{ translate('Stock') }} : <strong><span class="total-stock">{{ $product->total_stock }}</span></strong></span>
                </div>

            </div>
        </div>
        <div class="row pt-4">
            <div class="col-12">
                <?php
                $cart = false;
                if (session()->has('cart')) {
                    foreach (session()->get('cart') as $key => $cartItem) {
                        if (is_array($cartItem) && $cartItem['id'] == $product['id']) {
                            $cart = $cartItem;
                        }
                    }
                }
                ?>
                <h2>{{translate('description')}}</h2>
                <article>
                    <p class="d-block text-dark" id="description-{{ $product->id }}">
                    <span id="description-text-{{ $product->id }}">
                        {!! \App\CentralLogics\Helpers::trimWords($product->description)['text'] !!}
                    </span>
                        @if(Helpers::trimWords($product->description)['isTruncated'])
                            <a href="javascript:void(0);"
                               class="badge badge-soft-primary border-0 align-baseline fs-12 font-weight-light quick-view-see-more-button"
                               id="see-more-btn-{{ $product->id }}"
                               data-truncated="true">{{ translate('See More') }}</a>
                        @endif
                    </p>
                </article>
                <input type="hidden" name="id" value="{{ $product->id }}">
                @foreach (json_decode($product->choice_options) as $key => $choice)
                    <h3 class="mb-2 pt-4">{{ $choice->title }}</h3>
                    <div class="d-flex gap-3 flex-wrap">
                        @foreach ($choice->options as $key => $option)
                            <input class="btn-check" type="radio"
                                   id="{{ $choice->name }}-{{ $option }}"
                                   name="{{ $choice->name }}" value="{{ $option }}"
                                   @if($key == 0) checked @endif autocomplete="off">
                            <label class="check-label rounded px-2 py-1 text-center lh-1.3 mb-0 choice-input"
                                   for="{{ $choice->name }}-{{ $option }}">{{ $option }}</label>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    <div class="modal-body element-with-top-border-shadow">
        <div class="row ">
            <div class="col-12">
                <div class="row no-gutters text-dark d-flex align-items-center" id="chosen_price_div">
                    <div class="col">
                        <div class="product-description-label h5 font-weight-light mb-0">{{translate('Total Amount')}}:</div>
                    </div>
                    <div class="col">
                        <div class="product-price text-right text-primary h2 font-weight-bold mb-0">
                            <strong id="chosen_price"></strong> {{ Helpers::currency_symbol() }}
                        </div>
                    </div>
                </div>

                <div class="row no-gutters mt-4 text-dark d-flex align-items-center" id="chosen_price_div">
                    <div class="col">
                        <button class="btn btn-primary add-to-shopping-cart font-weight-bold w-100"
                                type="button">
                            <i class="tio-shopping-cart"></i>
                            {{translate('add to cart')}}
                        </button>
                    </div>
                    <div class="col d-flex justify-content-center">
                        <div class="product-quantity d-flex align-items-center">
                            <div class="d-flex justify-content-center align-items-center gap-3" id="quantity_div">
                                <button class="btn btn-number py-1 px-2 text-dark"type="button"
                                        data-type="minus" data-field="quantity"
                                        disabled="disabled">
                                    <i class="tio-remove font-weight-bold"></i>
                                </button>
                                <input type="text" name="quantity" id="quantity"
                                       class="form-control input-number text-center cart-qty-field w-25"
                                       placeholder="1" value="1" min="1">
                                <div class="tooltip-wrapper position-relative d-inline-block">
                                    <button class="btn btn-number py-1 px-2 text-dark" type="button" data-type="plus" data-field="quantity">
                                        <i class="tio-add font-weight-bold"></i>
                                    </button>

                                    <!-- Tooltip -->
                                    <div class="custom-tooltip">
                                        <div class="tooltip-body">
                                            <div class="tooltip-icon">⚠️</div>
                                            <div class="tooltip-content">
                                                <div class="h5 font-weight-light">{{translate("Warning")}}</div>
                                                <div class="fs-12">
                                                    There isn’t enough quantity on stock.<br>Only <span class="total-stock">{{ $product->total_stock }}</span> is available.
                                                </div>
                                            </div>
                                        </div>
                                        <span class="tooltip-close" onclick="this.parentElement.style.display='none'">&times;</span>
                                        <div class="tooltip-arrow"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $('.quick-view-see-more-button').click(function () {
            var button = $(this);
            var productId = button.attr('id').split('-').pop();
            var descriptionText = $('#description-text-' + productId);
            console.log(descriptionText, productId);
            var isTruncated = button.data('truncated');

            if (isTruncated) {
                var fullText = "{!! \App\CentralLogics\Helpers::trimWords($product->description, 0)["text"] !!}";
                console.log(fullText, descriptionText);
                descriptionText.html(fullText);
                button.text('{{ translate('See Less') }}');
                button.data('truncated', false);
            } else {
                var truncatedText = "{!! \App\CentralLogics\Helpers::trimWords($product->description, 50)["text"] !!}";
                descriptionText.html(truncatedText);
                button.text('{{ translate('See More') }}');
                button.data('truncated', true);
            }
        });
    });

    cartQuantityInitialize();
    getVariantPrice();
    $('#add-to-cart-form input').on('change', function () {
        getVariantPrice();
    });

    $('.add-to-shopping-cart').on('click', function () {
        addToCart();
    });

</script>
