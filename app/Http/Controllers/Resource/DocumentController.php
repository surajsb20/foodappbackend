<?php

namespace App\Http\Controllers\Resource;

use App\Document;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use auth;
use App\ShopBanner;
use App\Shop;
use App\Product;
use Exception;

class DocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $documents = Document::all();
        } catch (ModelNotFoundException $e) {
            return back()->with('flash_error', trans('form.whoops'));
        }

        return view('admin.documents.index', compact('documents'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        return view('admin.documents.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'type' => 'required',
        ]);
        try {
            Document::create([
                'name' => $request->name,
                'type' => $request->type
            ]);
            return back()->with('flash_success', trans('form.resource.created'));
        } catch (ModelNotFoundException $e) {
            return back()->with('flash_error', trans('form.not_found'));
        } catch (Exception $e) {
            return back()->with('flash_error', trans('form.whoops'));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $document = Document::find($id);

        return view('admin.documents.edit', compact('document'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
            'type' => 'required',
        ]);

        try {
            $document = Document::findOrFail($id);

            if ($request->has('name')) {
                $document['name'] = $request->name;
            }
            if ($request->has('type')) {
                $document['type'] = $request->type;
            }

            $document->update($document);

            // return redirect()->route('admin.products.index')->with('flash_success', 'Product updated!');
            return back()->with('flash_success', trans('form.resource.updated'));
        } catch (ModelNotFoundException $e) {
            // return redirect()->route('admin.products.index')->with('flash_error', 'Product not found!');
            return back()->with('flash_error', 'Product not found!');
        } catch (Exception $e) {
            // return redirect()->route('admin.products.index')->with('flash_error', trans('form.whoops'));
            return back()->with('flash_error', trans('form.whoops'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $document = Document::findOrFail($id);
            // Need to delete variants or have them re-assigned
            $document->delete();
            return back()->with('flash_success', trans('form.resource.deleted'));
        } catch (ModelNotFoundException $e) {
            return back()->with('flash_error', 'data not found!');
        } catch (Exception $e) {
            return back()->with('flash_error', trans('form.whoops'));
        }
    }
}
