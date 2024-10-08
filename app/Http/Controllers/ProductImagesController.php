<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductImagesController extends Controller
{
    /**
     * 商品图片的修改
     *
     * @param Product $product
     * @return Factory|View|Application
     */
    public function edit(Product $product): Factory|View|Application
    {
        return view('productimages.edit',compact('product'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $request->validate([
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('product_images', 'public');
                $product->images()->create(['image_url' => $path]);
            }
        }

        session()->flash('success','商品图片已经更新');
        return redirect()->route('products.edit', $product->id);
    }

    /**
     * 商品图片的删除
     *
     * @param Product $product
     * @param ProductImage $image
     * @return RedirectResponse
     */

    public function delete(Product $product, ProductImage $image): RedirectResponse
    {
        // 确保图片属于该产品
        if ($image->product_id !== $product->id) {
            session()->flash('error', '无效的操作');
            return redirect()->back();
        }

            // 删除文件系统中的图片文件
            if (Storage::disk('public')->exists($image->image_url)) {
                Storage::disk('public')->delete($image->image_url);
            }

            // 删除数据库记录
            $image->delete();

            // 设置成功消息
            session()->flash('success', '图片删除成功');

        // 重定向回产品编辑页面
        return redirect()->route('products.show', $product->id);
    }
}
