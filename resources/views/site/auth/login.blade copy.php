@extends('site.master')

@section('content')

<section class="form-checkout">
    <div class="container">
        <div class="row">
            <div class="login">
                <p class="col-md-4">Returning Customer?</p>
                <a href="#" class="bttn">LOGIN</a>
            </div>
            <div class="login">
                <p class="col-md-4" style="margin-top: 12px;">Have a coupon?</p>
                <input class="col-md-4" type="text" id="couponcode" name="couponcode" placeholder="Coupon code">
            </div>
            <div class="col-md-12 form-details" style="display:flex; justify-content: center;">
                <div class="col-md-6">
                    <div class="">
                        <h2 style="text-align: center;">Вход</h2>
                        <div class="col-md-6 item-ip">
                            <input type="text" id="firstname" name="firstname" placeholder="First name *">
                        </div>
                        <div class="col-md-6 item-ip">
                            <input type="text" id="lastname" name="lastname" placeholder="Last name *">
                        </div>
                        <div class="col-md-12 item-ip">
                            <input type="text" id="companyname" name="companyname" placeholder="Company name">
                        </div>
                        <div class="col-md-12 item-ip">
                            <select title="Pick a number" class="selectpicker">
                                <option>Country</option>
                                <option>op2</option>
                                <option>op3</option>
                            </select>
                        </div>
                        <div class="col-md-12 item-ip">
                            <input type="text" id="stressaddress" name="stressaddress" placeholder="Stress address * (House number and street name)">
                        </div>
                        <div class="col-md-12 item-ip">
                            <input type="text" id="postcode" name="postcode" placeholder="Postcode / ZIP *">
                        </div>
                        <div class="col-md-12 item-ip">
                            <input type="text" id="City" name="City" placeholder="Town / City *">
                        </div>
                        <div class="col-md-6 item-ip">
                            <input type="text" id="Phone" name="Phone" placeholder="Phone *">
                        </div>
                        <div class="col-md-6 item-ip">
                            <input type="text" id="Email" name="Email" placeholder="Email address *">
                        </div>
                        <input class="creat-chk" type="checkbox">Create an account?
                    </div>
                    <div class="method">
                        <h3>payment menthod</h3>
                        <form>
                            <input type="checkbox">Direct Bank Transfer
                            <p>Make your payment directly into our bank account. Please use your Order ID as the payment reference. Your order won’t be shipped until the funds have cleared in our account.</p>
                        </form>
                        <form style="margin-top: 0;">
                            <input type="checkbox">Paypal
                            <p>What's paypal?</p>
                        </form>
                    </div>
                    <form>
                        <input type="checkbox">I’ve read and accept the terms & conditions *</form>
                    <div class="btn-pay">
                        <div class="btn-black" style="width: 170px;">
                            <span>Proceed to pay</span>
                        </div>
                    </div>
                </div>
                <!-- <div class="col-md-6 check-right">
                    <form>
                        <input type="checkbox">ship to a different address?
                    </form>
                    <textarea placeholder="Order notes" id="message" class="form-control" name="comment[body]"></textarea>
                    <div class="order">
                        <h3>your order</h3>
                        <table class="cart-table">
                            <tbody>
                                <tr>
                                    <td>
                                        <a href="#"><img src="{{asset('assetsSite/images/prd_check_out.png')}}" alt=""></a>
                                        <div class="prd-infor">
                                            <a href="#">One shoulder bow shop</a>
                                            <p>Size: L</p>
                                        </div>
                                    </td>
                                    <td><span>x1</span></td>
                                    <td>
                                        <p>£250</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <a href="#"><img src="{{asset('assetsSite/images/prd_check_out1.png')}}" alt=""></a>
                                        <div class="prd-infor">
                                            <a href="#">One shoulder bow shop</a>
                                            <p>Size: L</p>
                                        </div>
                                    </td>
                                    <td><span>x1</span></td>
                                    <td>
                                        <p>£250</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="comunication">
                        <div>
                            <b>Subtotal: </b>
                            <span> £500</span>
                        </div>
                        <div>
                            <b>Shipping: </b>
                            <p>Shipping costs will be calculated once you have provided your address Contact us if you need any help.</p>
                        </div>
                        <div>
                            <b>TOTAL: </b>
                            <span> £500</span>
                        </div>
                    </div>
                </div> -->
            </div>
        </div>
    </div>
</section>
 
@endsection

