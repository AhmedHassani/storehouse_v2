{{-- Delivery Fee Modal --}}
<div class="modal fade" id="delivery-fee-modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{translate('رسوم التوصيل')}}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{route('admin.pos.delivery-fee')}}" method="POST" id="delivery-fee-form">
                    @csrf
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="free_delivery_checkbox"
                                name="is_free_delivery" value="1" onchange="toggleDeliveryFeeInput()">
                            <label class="custom-control-label" for="free_delivery_checkbox">
                                <strong>{{translate('توصيل مجاني')}}</strong>
                            </label>
                        </div>
                        <small class="text-muted">{{translate('إذا تم التحديد، سيكون التوصيل مجانياً')}}</small>
                    </div>

                    <div class="form-group" id="delivery_fee_amount_group">
                        <label for="delivery_fee_amount">{{translate('قيمة رسوم التوصيل')}}</label>
                        <input type="number" class="form-control" id="delivery_fee_amount" name="delivery_fee" min="0"
                            step="0.01" value="0" placeholder="{{translate('أدخل قيمة رسوم التوصيل')}}">
                        <small class="text-muted">{{translate('اترك فارغاً للتوصيل المجاني')}}</small>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button class="btn btn-secondary mr-2" type="button"
                            data-dismiss="modal">{{translate('إلغاء')}}</button>
                        <button class="btn btn-primary" type="submit">{{translate('حفظ')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleDeliveryFeeInput() {
        const checkbox = document.getElementById('free_delivery_checkbox');
        const amountGroup = document.getElementById('delivery_fee_amount_group');
        const amountInput = document.getElementById('delivery_fee_amount');

        if (checkbox.checked) {
            amountGroup.style.display = 'none';
            amountInput.value = '0';
        } else {
            amountGroup.style.display = 'block';
        }
    }
</script>