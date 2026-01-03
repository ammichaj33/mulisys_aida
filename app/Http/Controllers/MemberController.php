<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Member;
use Illuminate\Support\Facades\Auth;

class MemberController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Member::where('isActive', true);

        // Filtre par recherche (nom, prénom, téléphone, email, institution)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('firstName', 'like', "%{$search}%")
                  ->orWhere('lastName', 'like', "%{$search}%")
                  ->orWhere('phoneNumber', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('institutionFrom', 'like', "%{$search}%");
            });
        }

        // Filtre par genre
        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        // Filtre par statut (actif/inactif)
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('isActive', true);
            } elseif ($request->status === 'inactive') {
                $query->where('isActive', false);
            }
        }

        // Filtre par date de naissance (année)
        if ($request->filled('birth_year')) {
            $query->whereYear('birthDate', $request->birth_year);
        }

        $members = $query->orderBy('firstName')->paginate(20);

        return view('members.index', compact('members'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('members.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validationRules = [
            'firstName' => 'required|string|max:50',
            'lastName' => 'required|string|max:50',
            'phoneNumber' => 'required|string|max:20|unique:members,phoneNumber',
            'birthDate' => 'required|date',
            'gender' => 'required|in:M,F',
            'address' => 'required|string',
            'email' => 'nullable|email|max:100|unique:members,email',
            'institutionFrom' => 'required|string|max:100',
        ];

        // Ajouter la validation des fichiers seulement s'ils sont présents
        if ($request->hasFile('photo')) {
            $validationRules['photo'] = 'file|mimes:jpg,jpeg,png|max:1024';
        }
        
        if ($request->hasFile('idCard')) {
            $validationRules['idCard'] = 'file|mimes:pdf,jpg,jpeg,png|max:1024';
        }

        $request->validate($validationRules);

        $data = $request->all();
        
        // Créer le membre d'abord pour obtenir l'ID
        $member = Member::create($data);
        
        // Gérer l'upload de la photo
        if ($request->hasFile('photo')) {
            $photoFile = $request->file('photo');
            $extension = strtolower($photoFile->getClientOriginalExtension());
            $photoName = 'photo_id' . $member->memberId . '.' . $extension;
            $photoPath = $photoFile->storeAs('members/photos', $photoName, 'local');
            \Log::info('Photo uploaded: ' . $photoPath);
            $member->update(['photo' => $photoPath]);
        }
        
        // Gérer l'upload de la carte d'identité
        if ($request->hasFile('idCard')) {
            $idCardFile = $request->file('idCard');
            $extension = strtolower($idCardFile->getClientOriginalExtension());
            $idCardName = 'Identite_id' . $member->memberId . '.' . $extension;
            $idCardPath = $idCardFile->storeAs('members/idcards', $idCardName, 'local');
            \Log::info('ID Card uploaded: ' . $idCardPath);
            $member->update(['idCard' => $idCardPath]);
        }

        return redirect()->route('members.index')
            ->with('success', 'Membre créé avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $member = Member::with(['loanDocs' => function($query) {
            $query->orderBy('createdAt', 'desc');
        }])->findOrFail($id);

        return view('members.show', compact('member'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $member = Member::findOrFail($id);
        return view('members.edit', compact('member'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $member = Member::findOrFail($id);

        $validationRules = [
            'firstName' => 'required|string|max:50',
            'lastName' => 'required|string|max:50',
            'phoneNumber' => 'required|string|max:20|unique:members,phoneNumber,' . $id . ',memberId',
            'birthDate' => 'required|date',
            'gender' => 'required|in:M,F',
            'address' => 'required|string',
            'email' => 'nullable|email|max:100|unique:members,email,' . $id . ',memberId',
            'institutionFrom' => 'required|string|max:100',
        ];

        // Ajouter la validation des fichiers seulement s'ils sont présents
        if ($request->hasFile('photo')) {
            $validationRules['photo'] = 'file|mimes:jpg,jpeg,png|max:1024';
        }
        
        if ($request->hasFile('idCard')) {
            $validationRules['idCard'] = 'file|mimes:pdf,jpg,jpeg,png|max:1024';
        }

        $request->validate($validationRules);

        $data = $request->all();
        
        // Gérer l'upload de la photo
        if ($request->hasFile('photo')) {
            // Supprimer l'ancienne photo si elle existe
            if ($member->photo && \Storage::disk('local')->exists($member->photo)) {
                \Storage::disk('local')->delete($member->photo);
            }
            
            $photoFile = $request->file('photo');
            $extension = strtolower($photoFile->getClientOriginalExtension());
            $photoName = 'photo_id' . $member->memberId . '.' . $extension;
            $data['photo'] = $photoFile->storeAs('members/photos', $photoName, 'local');
        }
        
        // Gérer l'upload de la carte d'identité
        if ($request->hasFile('idCard')) {
            // Supprimer l'ancienne carte d'identité si elle existe
            if ($member->idCard && \Storage::disk('local')->exists($member->idCard)) {
                \Storage::disk('local')->delete($member->idCard);
            }
            
            $idCardFile = $request->file('idCard');
            $extension = strtolower($idCardFile->getClientOriginalExtension());
            $idCardName = 'Identite_id' . $member->memberId . '.' . $extension;
            $data['idCard'] = $idCardFile->storeAs('members/idcards', $idCardName, 'local');
        }

        $member->update($data);

        return redirect()->route('members.index')
            ->with('success', 'Membre mis à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $member = Member::findOrFail($id);
        $member->update(['isActive' => false]);

        return redirect()->route('members.index')
            ->with('success', 'Membre désactivé avec succès.');
    }

    /**
     * Servir la photo d'un membre de manière sécurisée
     */
    public function getPhoto($id)
    {
        $member = Member::findOrFail($id);
        
        // Vérifier que l'utilisateur a le droit de voir les membres
        if (!auth()->user()->can('view-members')) {
            abort(403, 'Accès non autorisé');
        }
        
        if (!$member->photo || !\Storage::disk('local')->exists($member->photo)) {
            abort(404, 'Photo non trouvée');
        }
        
        $filePath = storage_path('app/' . $member->photo);
        $mimeType = \Storage::disk('local')->mimeType($member->photo);
        
        return response()->file($filePath, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'private, max-age=3600'
        ]);
    }

    /**
     * Servir la carte d'identité d'un membre de manière sécurisée
     */
    public function getIdCard($id)
    {
        $member = Member::findOrFail($id);
        
        // Vérifier que l'utilisateur a le droit de voir les membres
        if (!auth()->user()->can('view-members')) {
            abort(403, 'Accès non autorisé');
        }
        
        if (!$member->idCard || !\Storage::disk('local')->exists($member->idCard)) {
            abort(404, 'Carte d\'identité non trouvée');
        }
        
        $filePath = storage_path('app/' . $member->idCard);
        $mimeType = \Storage::disk('local')->mimeType($member->idCard);
        
        return response()->file($filePath, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'private, max-age=3600'
        ]);
    }
}
