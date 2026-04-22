<div class="col-sm-6 col-lg-3">
    <a class="dashboard--card" href="{{route('admin.orders.list',['new'])}}">
        <h5 class="dashboard--card__subtitle">{{translate('new')}}</h5>
        <h2 class="dashboard--card__title">{{$data['new']}}</h2>
        <img width="30" src="{{asset('assets/admin/img/icons/pending.png')}}" class="dashboard--card__img" alt="{{ translate('new') }}">
    </a>
</div>

<div class="col-sm-6 col-lg-3">
    <a class="dashboard--card" href="{{route('admin.orders.list',['scheduled'])}}">
        <h5 class="dashboard--card__subtitle">{{translate('scheduled')}}</h5>
        <h2 class="dashboard--card__title">{{$data['scheduled']}}</h2>
        <img width="30" src="{{asset('assets/admin/img/icons/confirmed.png')}}" class="dashboard--card__img" alt="{{ translate('scheduled') }}">
    </a>
</div>

<div class="col-sm-6 col-lg-3">
    <a class="dashboard--card" href="{{route('admin.orders.list',['in-transit'])}}">
        <h5 class="dashboard--card__subtitle">{{translate('in-transit')}}</h5>
        <h2 class="dashboard--card__title">{{$data['in-transit']}}</h2>
        <img width="30" src="{{asset('assets/admin/img/icons/packaging.png')}}" class="dashboard--card__img" alt="{{ translate('in-transit') }}">
    </a>
</div>

<div class="col-sm-6 col-lg-3">
    <a class="dashboard--card" href="{{route('admin.orders.list',['out-for-delivery'])}}">
        <h5 class="dashboard--card__subtitle">{{translate('out_for_delivery')}}</h5>
        <h2 class="dashboard--card__title">{{$data['out-for-delivery']}}</h2>
        <img width="30" src="{{asset('assets/admin/img/icons/out_for_delivery.png')}}" class="dashboard--card__img" alt="{{ translate('out-for-delivery') }}">
    </a>
</div>


<div class="col-sm-6 col-lg-3">
    <a class="order-stats" href="{{route('admin.orders.list',['delivered'])}}">
        <div class="order-stats__content">
            <img width="20" src="{{asset('assets/admin/img/icons/delivered.png')}}" class="order-stats__img" alt="{{ translate('delivered') }}">
            <h6 class="order-stats__subtitle">{{ translate('delivered') }}</h6>
        </div>
        <span class="order-stats__title">{{$data['delivered']}}</span>
    </a>
</div>

<div class="col-sm-6 col-lg-3">
    <a class="order-stats" href="{{route('admin.orders.list',['postponed'])}}">
        <div class="order-stats__content">
            <img width="20" src="{{asset('assets/admin/img/icons/cancel.png')}}" class="order-stats__img" alt="{{ translate('postponed') }}">
            <h6 class="order-stats__subtitle">{{ translate('postponed') }}</h6>
        </div>
        <span class="order-stats__title">{{$data['postponed']}}</span>
    </a>
</div>

<div class="col-sm-6 col-lg-3">
    <a class="order-stats" href="{{route('admin.orders.list',['returned'])}}">
        <div class="order-stats__content">
            <img width="20" src="{{asset('assets/admin/img/icons/returned.png')}}" class="order-stats__img" alt="{{ translate('returned') }}">
            <h6 class="order-stats__subtitle">{{ translate('returned') }}</h6>
        </div>
        <span class="order-stats__title text-danger">{{$data['returned']}}</span>
    </a>
</div>

<div class="col-sm-6 col-lg-3">
    <a class="order-stats" href="{{route('admin.orders.list',['on-hold'])}}">
        <div class="order-stats__content">
            <img width="20" src="{{asset('assets/admin/img/icons/failed_to_deliver.png')}}" class="order-stats__img" alt="{{ translate('on-hold') }}">
            <h6 class="order-stats__subtitle">{{translate('on-hold')}}</h6>
        </div>
        <span class="order-stats__title text-danger">{{$data['on-hold']}}</span>
    </a>
</div>
