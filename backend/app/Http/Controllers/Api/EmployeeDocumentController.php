<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EmployeeDocumentController extends Controller
{
    public function store(Request $request, Employee $employee): JsonResponse
    {
        $data = $request->validate([
            'document_type' => 'required|string|max:100',
            'title' => 'required|string|max:255',
            'expiry_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'file' => 'required|file|max:10240|extensions:pdf,jpg,jpeg,png,doc,docx',
        ]);

        $file = $request->file('file');
        $ext = $file->getClientOriginalExtension();
        $data['file_path'] = $file->storeAs('employees/documents', Str::random(40) . ($ext ? '.' . $ext : ''), 'public');
        $data['employee_id'] = $employee->id;
        $data['uploaded_by'] = auth()->id();
        unset($data['file']);

        $document = EmployeeDocument::create($data);
        return response()->json($document->load('uploadedBy:id,name'), 201);
    }

    public function destroy(EmployeeDocument $document): JsonResponse
    {
        if ($document->file_path) {
            Storage::disk('public')->delete($document->file_path);
        }
        $document->delete();
        return response()->json(['message' => 'Document deleted.']);
    }

    public function stream(EmployeeDocument $document)
    {
        abort_unless($document->file_path && Storage::disk('public')->exists($document->file_path), 404);
        $mimes = ['pdf' => 'application/pdf', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'doc' => 'application/msword', 'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        $ext = strtolower(pathinfo($document->file_path, PATHINFO_EXTENSION));
        return response(Storage::disk('public')->get($document->file_path), 200, [
            'Content-Type' => $mimes[$ext] ?? 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="' . addslashes($document->title) . '.' . $ext . '"',
            'Cache-Control' => 'private, max-age=86400',
        ]);
    }
}
