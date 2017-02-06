@extends('layouts.email')

@section('title', $subject)

@section('styles')
<style>
    .content table {
        width: 100%;
    }
    .pad-top { padding-top : 1em; }

    .table {
        width : 100%;
    }
    .table tr th {
        text-align : left;
    }
</style>
@endsection

@section('body')
<section class="content">
    <p>Hi {{ $name }},</p>

    <p>Welcome to Passport to Prana! Your membership will give you access to the best studios, teachers and classes in the city.</p>

    <p>Order Confirmation: {{ $charge_id }}</p>

    <p><em>Please retain a copy of this number for future reference and enquiries.</em></p>

    <div>
        <table class="table cart">
            <thead>
                <tr>
                    <th>Product </th>
                    <th>Quantity </th>
                    <th class="amount">Total </th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                <tr>
                    <td class="product">
                        <span>{{ $item['name'] }}</span>
                    </td>
                    <td class="price">
                        <small>{{ $item['quantity'] }}</small>
                    </td>
                    <td class="amount">${{ $item['subtotal'] }}</td>
                </tr>
                @endforeach
                <tr><td></td></tr>
                <tr>
                    <td class="total-quantity" colspan="2">Subtotal</td>
                    <td class="amount">${{ $subtotal }}</td>
                </tr>
                <tr>
                    <td class="total-quantity" colspan="2">Shipping</td>
                    <td class="amount">${{ $shipping }}</td>
                </tr>
                <tr>
                    <td class="total-quantity" colspan="1">Taxes</td>
                    <td class="amount">{{ $taxrate }}%</td>
                    <td class="amount">${{ $taxes }}</td>
                </tr>
                <tr>
                    <td class="total-quantity" colspan="2">
                        Total
                    </td>
                    <td class="total-amount">${{ $total }}</td>
                </tr>
            </tbody>
        </table>

    </div>

    @if($address)
    <p>Your order will be shipped to: <em>{{ $address }}</em></p>
    <p>Once you receive your membership card, add it to your account on the My Cards page before going to your first class.</p>
    @endif
    <p>If you have any questions about your order, email us anytime at <a href="mailto:info@passporttoprana.com">info@passporttoprana.com</a>. We are always happy to hear from you!</p>
    
</section>

@stop
