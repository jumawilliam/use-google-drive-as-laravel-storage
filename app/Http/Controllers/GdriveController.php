<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class GdriveController extends Controller
{
    public function token()
    {
        $client_id = \Config('services.google.client_id');
        $client_secret = \Config('services.google.client_secret');
        $refresh_token = \Config('services.google.refresh_token');
        $folder_id = \Config('services.google.folder_id');

        $response = Http::post('https://oauth2.googleapis.com/token', [

            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'refresh_token' => $refresh_token,
            'grant_type' => 'refresh_token',

        ]);
        //dd($response);
        $accessToken = json_decode((string) $response->getBody(), true)['access_token'];

        return $accessToken;
    }

    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $files = File::all();

        return view('create', compact('files'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validation = $request->validate([
            'file' => 'file|required',
            'file_name' => 'required',
        ]);

        $accessToken = $this->token();
        //dd($accessToken);
        $name = $request->file->getClientOriginalName();
        //$mime=$request->file->getClientMimeType();

        $path=$request->file->getRealPath();




        $response=Http::withToken($accessToken)
        ->attach('data',file_get_contents($path),$name)
        ->post('https://www.googleapis.com/upload/drive/v3/files',
            [
                'name'=>$name
            ],
            [
                'Content-Type'=>'application/octet-stream',
            ]
            );

            //dd($response);



            if ($response->successful()) {
                $file_id = json_decode($response->body())->id;
                //dd($name);
                $uploadedfile = new File;
                $uploadedfile->file_name = $request->file_name;
                $uploadedfile->name=$name;
                $uploadedfile->fileid = $file_id;
                $uploadedfile->save();

                return response('File Uploaded to Google Drive');
            }


        return response('Failed to Upload to Google Drive');
    }

    /**
     * Display the specified resource.
     */
    public function show(File $file)
    {
        $ext=pathinfo($file->name,PATHINFO_EXTENSION);
        $accessToken=$this->token();

        $response=Http::withHeaders([
            'Authorization'=> 'Bearer '.$accessToken,
        ])->get("https://www.googleapis.com/drive/v3/files/{$file->fileid}?alt=media");

        if($response->successful()){
            $filePath='/downloads/'.$file->file_name.'.'.$ext;

            Storage::put($filePath,$response->body());

            return Storage::download($filePath);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
