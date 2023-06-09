<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\CertificateModel;
use App\Models\UserHasCertificateModel;
use App\Models\UserHasCompanyModel;
use App\Models\CompanyModel;

class User extends BaseController
{
    public function login()
    {
        $data = [
            "title" => "Connexion",
            'isLoggedIn' => false,
        ];
        helper(['form']);

        if ($this->request->getMethod() == 'post') {
            //let's do the validation here
            $rules = [
                'mail' => 'required|min_length[6]|max_length[50]|valid_email',
                'password' => 'required|min_length[8]|max_length[255]',
            ];

            $error = [
                'mail' => [
                    'required' => "Adresse mail vide!",
                    'valid_email' => 'Format mail incorrect.',
                ],
                'password' => ['required' => "Mode de passe requis!"],
            ];

            if (!$this->validate($rules, $error)) {
                $data['validation'] = $this->validator;
            } else {
                $model = new UserModel();

                $user = $model->where('mail', $this->request->getVar('mail'))->first();

                if (!$user) {
                    session()->setFlashdata('infos', 'Cet utilisateur n\'existe pas');
                } else {
                    $pw = $this->request->getVar('password');
                    $pwh = $user['password'];

                    if (password_verify($pw, $pwh)) {
                        $this->setUserSession($user);
                        return redirect()->to("dashboard");
                    }
                }
            }
        }
        return view('Login/login', $data);
    }

    private function setUserSession($user)
    {
        $data = [
            'id' => $user['id_user'],
            'name' => $user['name'],
            'firstname' => $user['firstname'],
            'mail' => $user['mail'],
            'isLoggedIn' => true,
        ];
        session()->set($data);
        return true;
    }


    private function setCompanySession($user, $company)
    {
        $data = [
            'user_name' => $user['name'],
            'user_firstname' => $user['firstname'],
            'user_mail' => $user['mail'],
            'user_address' => $user['address'],
            'user_cp' => $user['cp'],
            'user_city' => $user['city'],
            'user_phone' => $user['phone'],
            'user_password' => $user['password'],
            'company_name' => $company['name'],
            'company_address' => $company['address'],
            'company_cp' => $company['cp'],
            'company_city' => $company['city'],
            'company_kbis' => $company['kbis'],
            'company_siret' => $company['siret'],
        ];
        session()->set($data);
        return true;
    }

    private function saveCompany($data_user, $data_company, $kbis, $siret)
    {
        $modelu = new UserModel();
        $modelcp = new CompanyModel();
        $modelucf = new UserHasCompanyModel();

        //table utilisateur
        $modelu->save($data_user);

        // table entreprise    
        $data_company['siret'] = $siret;
        $data_company['kbis'] = $kbis;
        $modelcp->save($data_company);

        // table jointure
        $id_user = $modelu->getInsertID();
        $id_company = $modelcp->getInsertID();
        $data_jointure = [
            "id_user" => $id_user,
            "id_company" => $id_company,
        ];
        $modelucf->save($data_jointure);
    }

    private function associateCompany($data_user, $id_company, $kbis, $siret)
    {
        $modelu = new UserModel();
        $modelcp = new CompanyModel();
        $modelucf = new UserHasCompanyModel();

        //table utilisateur
        $modelu->save($data_user);      

        // table jointure
        $id_user = $modelu->getInsertID();
       
        $data_jointure = [
            "id_user" => $id_user,
            "id_company" => $id_company,
        ];
        $modelucf->save($data_jointure);
    }
    private function ifNotExistCompany($data_company)
    {
        $modelcp = new CompanyModel();
        $company = $modelcp->where("name", $data_company['name'])
            ->where("cp", $data_company['cp'])
            ->where("city", $data_company['city'])
            ->first();
        return $company;
    }

    public function confirmation()
    {
        helper(['form']);

        $data['title'] = "Inscrire entreprise";

        $data_user = [
            "name" => session()->get("user_name"),
            "firstname" => session()->get("user_firstname"),
            "address" => session()->get("user_address"),
            "cp" => session()->get("user_cp"),
            "city" => session()->get("user_city"),
            'phone' =>  session()->get('user_phone'),
            'password' =>  session()->get('user_password'),
            'mail' => session()->get('user_mail'),
        ];

        $data_company = [
            'name' => session()->get('company_name'),
            'address' => session()->get('company_address'),
            'city' => session()->get('company_city'),
            'cp' => session()->get('company_cp'),
        ];


        if ($this->request->getMethod() == 'post') {

            $rulesconf = [
                'c_siret' => 'required|min_length[12]|max_length[14]',
                'c_kbis' => 'required|min_length[3]|max_length[64]',
            ];

            $errorconf = [
                'c_siret' => ['required' => "Siret de la compagnie vide!"],
                'c_kbis' => ['required' => "Kbis de la compagnie vide!"],
            ];

            $kbis = $this->request->getVar('c_kbis');
            $siret = $this->request->getVar('c_siret');

            if (!$this->validate($rulesconf, $errorconf)) {
                $data['validation'] = $this->validator;
                $data['title'] = "Inscrire société";
                return view('Login/confirmation', $data);
            } else {
                $company=$this->ifNotExistCompany($data_company);
                if ($company==null){//la société n'existe pas on la rajoute
                    $this->saveCompany($data_user, $data_company, $kbis, $siret);                   
                }
                else{// la société existe donc on associe juste avec la table de jointure
                    $this->associateCompany($data_user, $company['id_company'], $kbis, $siret);   
                }
                $data["title"] = "Login";
                return view('Login/login', $data);
            }
        }
    }


    public function profileuser()
    {
        $db      = \Config\Database::connect();
        $builder = $db->table('user');
        $id = 3;

        $builder->where('id_user', $id);
        $query   = $builder->get();
        $user = $query->getResultArray();
        $user = $user[0]; // juste le premier 

        $data = [
            "title" => "Membre",
            "user" => $user,
        ];

        return view('User/profile_user.php', $data);
    }

    public function profilecompany()
    {
        $db      = \Config\Database::connect();
        $builder = $db->table('user');
        $id = 3;

        $builder->where('id_user', $id);
        $query   = $builder->get();
        $user = $query->getResultArray();
        $user = $user[0]; // juste le premier 


        $builder->select('company.name, company.address,company.city ,company.cp');
        $builder->join('user_has_company', 'user_has_company.id_user = user.id_user');
        $builder->join('company', 'user_has_company.id_company=company.id_company');
        $query = $builder->get();
        $infos = $query->getResultArray();

        $company = [];
        foreach ($infos as $info) {
            $company[] = [
                "name" => $info['name'],
                "address" => $info['address'] . "<br>" . $info['city'] . ", " . $info['cp']
            ];
        }

        $data = [
            "title" => "Membre",
            "user" => $user,
            "company" => $company,
        ];
        return view('User/profile_company.php', $data);
    }

    public function forgetpassword()
    {
        $data = [
            "title" => "Mot de passe oublié"
        ];
        return view('Login/forgetpassword.php', $data);
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/');
        //return view('Home/index.php');
    }


    public function signin()
    {
        $data = ["title" => "Inscription"];
        helper(['form']);

        if ($this->request->getMethod() == 'post') {

            $index = $this->request->getVar('index');
            $check = $this->request->getVar('newsletters');
            $main = true;
            $sub = true;

            $rules = [
                'name' => 'required|min_length[3]|max_length[20]',
                'firstname' => 'required|min_length[3]|max_length[20]',
                'address' => 'required|min_length[3]|max_length[128]',
                'city' => 'required|min_length[3]|max_length[64]',
                'cp' => 'required|min_length[3]|max_length[16]',
                'country' => 'required|min_length[3]|max_length[16]',
                'phone' => 'required|min_length[3]|max_length[16]',
                'mail' => 'required|min_length[6]|max_length[50]|valid_email|is_unique[user.mail]',
                'password' => 'required|min_length[8]|max_length[255]',
                'password_confirm' => 'matches[password]',
            ];
            $error = [
                'name' => ['required' => "Nom vide!"],
                'firstname' => ['required' => "Prénom vide!"],
                'address' => ['required' => "Adresse vide!"],
                'city' => ['required' => "Ville vide!"],
                'cp' => ['required' => "Code postal vide!"],
                'country' => ['required' => "Pays vide!"],
                'phone' => ['required' => "Numéro de téléphone vide!"],
                'mail' => ['required' => "Adresse mail vide!"],
                'password' => ['required' => "Mot de passe requis!"],
            ];

            $rulesf = [
                'f_name' => 'required|min_length[3]|max_length[20]',
                'f_content' => 'required|min_length[3]|max_length[128]',
                'f_date' => 'required|min_length[3]|max_length[128]',
                'f_organism' => 'required|min_length[3]|max_length[128]',
                'f_address' => 'required|min_length[3]|max_length[128]',
                'f_city' => 'required|min_length[3]|max_length[64]',
                'f_cp' => 'required|min_length[3]|max_length[16]',
                'f_country' => 'required|min_length[3]|max_length[16]',
            ];
            $errorf = [
                'f_name' => ['required' => "Nom de la certification vide!"],
                'f_content' => ['required' => "Contenu de la certification vide!"],
                'f_date' => ['required' => "Date de la certification vide!"],
                'f_organism' => ['required' => "Organisme de la certification vide!"],
                'f_address' => ['required' => "Adresse de la certification vide!"],
                'f_city' => ['required' => "Ville de la certification vide!"],
                'f_cp' => ['required' => "Code postal de la certification vide!"],
                'f_country' => ['required' => "Pays de la certification vide!"],
            ];

            $rulesc = [
                'c_name' => 'required|min_length[3]|max_length[20]',
                'c_address' => 'required|min_length[3]|max_length[128]',
                'c_city' => 'required|min_length[3]|max_length[64]',
                'c_cp' => 'required|min_length[3]|max_length[16]',
                //'c_siret' => 'required|min_length[3]|max_length[64]',
                //'c_kbis' => 'required|min_length[3]|max_length[64]',
            ];
            $errorc = [
                'c_name' => ['required' => "Nom de la compagnie vide!"],
                'c_address' => ['required' => "Adresse de la compagnie vide!"],
                'c_city' => ['required' => "Ville de la compagnie vide!"],
                'c_cp' => ['required' => "Code postal de la compagnie vide!"],
                //'c_siret' => ['required' => "Siret de la compagnie vide!"],
                //'c_kbis' => ['required' => "Kbis de la compagnie vide!"],
            ];

            if (!$this->validate($rules, $error)) {
                $data['validation'] = $this->validator;
                $main = false;
            }

            if (!$this->validate($rules, $error)) {
                $data['confirmation'] = $this->validator;
                $main = false;
            }

            switch ($index) {
                case "1":
                    echo "<p>Veuillez sélectionner votre catégorie</p>";
                    $sub = false;
                    break;
                case "2":
                    if (!$this->validate($rulesf, $errorf)) {
                        $data['validation'] = $this->validator;
                        $sub = false;
                    } else {
                        $status = '7';
                    }
                    break;
                case "3":
                    if (!$this->validate($rulesc, $errorc)) {
                        $data['validation'] = $this->validator;
                        $sub = false;
                    } else {
                        $status = '8';
                    }
                    break;
                case "4":
                    $status = '4';
                    break;
            }
            $ischecked = ($check == NULL) ? 0 : 1;

            if ($main && $sub) {

                $model = new UserModel();
                $newData = [
                    'name' => $this->request->getVar('name'),
                    'firstname' => $this->request->getVar('firstname'),
                    'address' => $this->request->getVar('address'),
                    'city' => $this->request->getVar('city'),
                    'cp' => $this->request->getVar('cp'),
                    'country' => $this->request->getVar('country'),
                    'type' => $status,
                    'phone' => $this->request->getVar('phone'),
                    'mail' => $this->request->getVar('mail'),
                    'password' => $this->request->getVar('password'),
                    'newsletters' => $ischecked,
                ];

                if ($index == 4) {
                    $model->save($newData);
                }

                if ($index == 2) {
                    $model->save($newData);
                    $id_user = $model->getInsertID();

                    $modelf = new CertificateModel();

                    $newDataf = [
                        'name' => $this->request->getVar('f_name'),
                        'content' => $this->request->getVar('f_content'),
                        'date' => $this->request->getVar('f_date'),
                        'organism' => $this->request->getVar('f_organism'),
                        'address' => $this->request->getVar('f_address'),
                        'city' => $this->request->getVar('f_city'),
                        'cp' => $this->request->getVar('f_cp'),
                        'country' => $this->request->getVar('f_country'),
                    ];
                    $modelf->save($newDataf);

                    $modelce = new UserHasCertificateModel();

                    $id_certificate = $modelf->getInsertID();

                    $newDatace = [
                        'id_user' => $id_user,
                        'id_certificate' => $id_certificate,
                    ];

                    $modelce->save($newDatace);
                }
                if ($index == 3) {
                    $kbis = $this->request->getVar('c_kbis');
                    $siret = $this->request->getVar('c_siret');

                    $newDatac = [
                        'name' => $this->request->getVar('c_name'),
                        'address' => $this->request->getVar('c_address'),
                        'city' => $this->request->getVar('c_city'),
                        'cp' => $this->request->getVar('c_cp'),
                        'kbis' => $kbis,
                        'siret' => $siret,
                    ];

                    if (empty($kbis) || empty($siret)) {
                        $data['title'] = "Inscrire entreprise";
                        $this->setCompanySession($newData, $newDatac);
                        return view("Login/confirmation", $data);
                    } else {

                        $this->saveCompany($newData, $newDatac, $kbis, $siret);
                    }
                }
                session()->setFlashdata('success', 'Inscription réussi');
                return view("Login/login");
            }
        }
        return view('Login/signin', $data);
    }
}
