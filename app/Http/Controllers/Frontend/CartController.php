<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class CartController extends Controller
{
    public function ajaxCart(Request $request)
    {
        $cart = session('cart', []);
        $productId = $request->product_id;

        if (isset($cart[$productId])) {
            $cart[$productId]['qty'] += 1;
        } else {
            $product = Product::findOrFail($productId);
            $cart[$productId] = [
                'productId' => $productId,
                'qty' => 1,
                'price' => $product->price,
                'name' => $product->name,
                'image' => $product->image_src
            ];
        }

        session()->put('cart', $cart);
        return response()->json(['cart' => $cart]);
    }

    public function showCart()
    {
        $cart = session()->get('cart', []);
        $grandTotal = $this->calcGrandTotal($cart);

        return view('frontend.cart.index', compact('cart', 'grandTotal'));
    }

    public function upQty(Request $request)
    {
        $cart = session()->get('cart', []);
        $productId = $request->productId;

        if (Arr::has($cart, $productId)) {
            $cart[$productId]['qty']  += 1;
        }

        session()->put('cart', $cart);
        $grandTotal = $this->calcGrandTotal($cart);

        return response()->json(['grandTotal' => $grandTotal]);
    }

    public function downQty(Request $request)
    {
        $cart = session()->get('cart', []);
        $productId = $request->productId;

        if (Arr::has($cart, $productId)) {
            if ($cart[$productId]['qty'] > 0) {
                $cart[$productId]['qty']  -= 1;
            }
        }

        session()->put('cart', $cart);
        $grandTotal = $this->calcGrandTotal($cart);

        return response()->json(['grandTotal' => $grandTotal]);
    }

    public function changeQty(Request $request)
    {
        $cart = session()->get('cart', []);
        $productId = $request->productId;
        $product = Product::find($productId);
        $qty = $request->qty;

        if (Arr::has($cart, $productId)) {
            if ($cart[$productId]['qty'] > 0) {
                $cart[$productId]['qty']  += $qty;
            }
        } else {
            $cart[$productId] = [
                "productId" => $productId,
                "qty" => $qty,
                "price" => $product->price,
                'name' => $product->name,
                'image' => $product->image_src
            ];


        }
        session()->put('cart', $cart);

        $grandTotal = $this->calcGrandTotal($cart);

        return response()->json(['grandTotal' => $grandTotal, 'cart' => $cart]);

    }

    public function deleteCart (Request $request) {
        $cart = session()->get('cart', []);
        $productId = $request->productId;

        if (Arr::has($cart, $productId)) {
           unset($cart[$productId]);
        }
        session()->put('cart', $cart);
        $grandTotal = $this->calcGrandTotal($cart);

        return response()->json(['grandTotal' => $grandTotal, 'cart' => $cart]);
    }
    protected function calcGrandTotal($cart)
    {
        $grandTotal = 0;
        foreach ($cart as $key => $value) {
            $grandTotal += $value['price'] * $value['qty'];
        }

        return $grandTotal;
    }
}
