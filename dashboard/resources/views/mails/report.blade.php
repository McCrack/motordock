<div style="max-width: 720px">
    <div style="text-align:center;padding: 10px;background-color:#152532;color:white">
        <h1>MOTORDOCK</h1>
        <h3>Report {{ date("d M, Y [H:i]") }}</h3>
    </div>
    <br>
    <div style="padding: 1px 10px;background-color:#A0D0F0;color:white">
        <h3>New Items: {{ $newItems }}</h3>
    </div>
    <div style="padding: 1px 10px;background-color:#80C0E0;color:white">
        <h3>Completed Items: {{ $completedItems }}</h3>
    </div>
    <h1>Orders</h1>
    @foreach($orders as $order)
    @php
    $total = 0;
    @endphp
    <div style="padding: 1px 10px;background-color:#EC5051;color:white">
        <h3>Bestellnummer {{ $order->order_number }}</h3>
    </div>
    <table width="100%" cellspacing="0" cellpadding="8">
        <tbody>
            <tr align="left" bgcolor="#F3F3F3">
                <td width="150px">Name:</td>
                <th>{{ $order->name }} {{ $order->last_name }}</th>
            </tr>
            <tr align="left">
                <td>Telefonnummer:</td>
                <th><a href="tel:{{ $order->phone }}">{{ $order->phone }}</a></th>
            </tr>
            <tr align="left" bgcolor="#F3F3F3">
                <td>Email:</td>
                <th>{{ $order->email }}</th>
            </tr>
            <tr align="left">
                <td>Ansehen:</td>
                <th>{{ $order->reputation }}</th>
            </tr>
            <tr align="left" bgcolor="black" style="color:white">
                <td colspan="2">Lieferadresse</td>
            </tr>
            <tr align="left">
                <td>Postleitzahl:</td>
                <th>{{ $order->delivery['postcode'] }}</th>
            </tr>
            <tr align="left" bgcolor="#F3F3F3">
                <td>Stadt:</td>
                <th>{{ $order->delivery['city'] }}</th>
            </tr>
            <tr align="left">
                <td style="white-space:nowrap">Straße und Hausnummer:</td>
                <th>{{ $order->delivery['address'] }}</th>
            </tr>
        </tbody>

    </table>
    <div style="padding:15px 10px;border:1px solid #CCC;border-radius:3px">
        <h4 style="margin:0">Kommentar zur Bestellung</h4>
        <p>
        {{ $order->message }}
        </p>
    </div>
    <br>
    <table width="100%" cellspacing="0" cellpadding="6">
        <tbody>
            @foreach($order->items as $item)
            @php
            $total += $item->selling;
            @endphp
            <tr bgcolor="#222222" style="padding:10px;color:white">
                <td colspan = 2><b>{{ $item->named }}</b></td>
            </tr>
            <tr>
                <td width="100">
                    <img src="{{ $item->preview }}" height="100">
                </td>
                <td align="left">
                    <div>Status: 
                        @if($item->status!="Active")
                        <b style="color:#AA0022">{{ $item->status }}</b>
                        @else
                        <b style="color:#00AA88">{{ $item->status }}</b>
                        @endif
                    </div>
                    <div>Preis: € <b>{{ $item->selling }}</b></div>
                    <div><small>Verkäufer: <b>{{ $item->StoreName }}</b> [<b>#{{ $item->tag }}</b>]</small></div>
                    <div><small>{{ $item->link }}</small></div>
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th align="right" colspan="2" style="border-top:1px solid #CCC">
                    Total: € {{ $total }} + {{ $order->delivery_price }}
                </th>
            </tr>
        </tfoot>
    </table>
    @endforeach
</div>