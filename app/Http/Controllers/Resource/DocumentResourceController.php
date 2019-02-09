<?php

namespace App\Http\Controllers\Resource;

use App\Transporter;
use App\TransporterDocument;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DocumentResourceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id)
    {

        //
        $transporter = Transporter::find($id);

        //
        $transporterDocs = TransporterDocument::where('transporter_id', $id)->get();

        if ($transporterDocs) {
            return view('admin.transporters.documents.index', compact('transporterDocs', 'transporter'));
        } else {
            return redirect()->back()->withErrors('Transporter does not have any documents');
        }

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
     * @param $transporter_id
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
        $transporterDoc = TransporterDocument::find($id);

        //
        return view('admin.transporters.documents.edit', compact('transporterDoc'));

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $provider, $id)
    {
        //
        $document = TransporterDocument::where('transporter_id', $provider)
            ->find($id);

        if ($document) {

            $document->status = 'ACTIVE';
            $document->save();

        } else {
            return redirect()->back()->withErrors('Provider not found!');
        }

        return redirect()
            ->back()->with('flash_success', 'Provider document has been approved.');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
