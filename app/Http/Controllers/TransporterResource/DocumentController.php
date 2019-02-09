<?php

namespace App\Http\Controllers\TransporterResource;

use App\Document;
use App\Http\Controllers\Controller;
use App\Http\Requests\DocumentUploadRequest;
use App\TransporterDocument;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{

    //
    public function index()
    {
        $provider = auth()->user();

        if ($provider) {

            $providerDocuments = $provider->documents;
            $documents = Document::all();

            $response = ['documents' => $documents, 'providerDocuments' => $providerDocuments];

            return response()->json($response);

        } else {
            return response()->json('error', 'Provider not found.');
        }


    }

    //
    public function uploadDocument(DocumentUploadRequest $request, $id)
    {

        $provider = auth()->user();

        $providerDocument = TransporterDocument::where('transporter_id', $provider->id)
            ->where('document_id', $id)
            ->first();

        if (!$providerDocument) {
            $providerDocument = new TransporterDocument();
            $providerDocument->transporter_id = $provider->id;
            $providerDocument->document_id = $id;
            $providerDocument->unique_id = rand(100000, 999999);
        }

        $providerDocument->status = 'ASSESSING';

//        if ($request->image != "") {
        Storage::delete($providerDocument->url);
        $providerDocument->url = $request->image->store('provider/documents');
//        } else {
//            return response()->json(['error' => 'Document upload failed']);
//        }

        $providerDocument->save();

        if ($providerDocument) {
            return response()->json(['message' => 'Document update successfully']);
        } else {
            return response()->json(['error' => 'Document upload failed.']);
        }

    }

    //
    public function checkSubmittedDocuments()
    {

        $provider = auth()->user();

        $providerDocuments = $provider->documents;

        $docs = Document::all();

        if ($providerDocuments->count() == $docs->count()) {
            return response()->json(['message' => 'All document submitted']);
        } else {
            return response()->json(['error' => 'Please submit all documents.']);
        }

    }

}
