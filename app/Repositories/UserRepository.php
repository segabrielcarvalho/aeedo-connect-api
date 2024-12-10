<?php

namespace App\Repositories;

use App\Enums\Role;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserRepository extends BaseRepository
{
  public function __construct(private PatientRepository $patientRepository){}

  public function storeUser(array $data): void
  {
    DB::beginTransaction();
    try {
      if ($data['role'] == Role::ADMIN->value) {
        $user = $this->registerAdmin($data);
      }
      else {
        $user = $this->registerUser($data);
      }

      DB::commit();

      $this->setStatus(true);
      $this->setStatusCode(Response::HTTP_CREATED);
      $this->setData($user);
    } catch (\Exception $e) {
      DB::rollBack();
      $this->setStatus(false);
      $this->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
      $this->setErrorMessage('Não foi possível cadastrar usuário');
    }
  }

  public function updateUser(string $id, array $data): void
  {
    DB::beginTransaction();
    try {
      $user = $this->findUser($id);
      if (empty($user)) return;

      $userArr = [];
      if (!empty($data['name']))
        $userArr['name'] = $data['name'];

      if (!empty($data['email']))
        $userArr['email'] = $data['email'];

      if (!empty($data['password']))
        $userArr['password'] = Hash::make($data['password']);

      if (!empty($data['role']))
        $userArr['role'] = $data['role'];

      if (isset($data['is_active']))
        $userArr['is_active'] = $data['is_active'];

      $user->fill($userArr);
      $user->save();

      $patient = $this->patientRepository->findPatientByUserId($user->id);

      if (!empty($data['patient_type']))
        $patient->patient_type = $data['patient_type'];

      if (!empty($data['blood_type']))
        $patient->blood_type = $data['blood_type'];

      $patient->user()->associate($user);
      $patient->save();

      if (!empty($data['organs'])) {
        $organIds = array_column($data['organs'], 'id');
        $patient->organs()->sync($organIds);
      }

      $this->setStatus(true);
      $this->setStatusCode(Response::HTTP_OK);
      $this->setData($user);

      DB::commit();
    } catch (\Exception $e) {
      DB::rollBack();
      $this->setStatus(false);
      $this->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
      $this->setErrorMessage("Não foi possível atualizar este usuário");
    }
  }

  public function findUserByEmail(string $email): ?User
  {
    try {
      $user = User::where('email', $email)
        ->where('is_active', true)
        ->first();

      $this->setStatus(true);
      $this->setStatusCode(Response::HTTP_OK);
      $this->setData($user);
      return $user;
    } catch (\Exception $e) {
      $this->setStatus(false);
      $this->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
      $this->setErrorMessage('Não foi possível encontrar este dado');
    }
  }

  private function registerAdmin(array $adminData): User
  {
    $adminData['password'] = Hash::make($adminData['password']);
    $admin = User::create($adminData);

    return $admin;
  }

  private function registerUser(array $userData): User
  {
      $isActive = $userData['is_active'] ?? true;
  
      $user = User::create([
          'name' => $userData['name'],
          'email' => $userData['email'],
          'password' => $userData['password'],
          'role' => $userData['role'],
          'is_active' => $isActive,
      ]);
  
      $patient = new Patient();
      $patient->patient_type = $userData['patient_type'] ?? null;
      $patient->blood_type = $userData['blood_type'] ?? null;
      $user->patient()->save($patient);
  
      if (!empty($userData['organs'])) {
          $organIds = array_column($userData['organs'], 'id');
          $patient->organs()->attach($organIds);
      }
  
      return $user;
  }
  

  public function filterUsers(array $request): LengthAwarePaginator
  {
    $users = User::where('is_active', true);

    if (isset($request['type'])) {
      $users->when($request['type'])
        ->where('is_active', true)
        ->whereHas('patient', function($query) use ($request) {
            $query->where('patient_type', $request['type']);
        });
    }

    $users->when(!empty($request['email']), function($query) use ($request) {
      return $query->where('email', 'like', '%'.$request['email'].'%');
    });

    $users
      ->orderBy('role', 'DESC')
      ->orderBy('name', 'ASC');

    $users = $users->paginate(10);
    // dd($users->get());

    return $users;
  }

  public function findUser(string $id): ?User
  {
    $user = User::find($id);
    if (empty($user)) {
      $this->setStatus(false);
      $this->setErrorMessage("Usuário não encontrado");
      $this->setStatusCode(Response::HTTP_NOT_FOUND);

      return null;
    }
   
    $this->setStatus(true);
    return $user;
  }
}