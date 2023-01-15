<?php

namespace App\Controllers;

require_once($_SERVER['DOCUMENT_ROOT'] . '/php/functions/util.php');

use App\Models\UserModel;
use App\Models\CertificateModel;
use App\Models\UserHasCertificateModel;
use App\Models\UserHasCompanyModel;
use App\Models\CompanyModel;
use App\Libraries\UserHelper;
use App\Libraries\BillHelper;
//use CodeIgniter\HTTP\Message;

// le 12/01/2023
class User extends BaseController
{
    /* connexion utlitisateur */
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
                'password' => 'required|min_length[8]|max_length[30]',
            ];

            $error = [
                'mail' => [
                    'required' => "Adresse mail vide!",
                    'min_length' => 'Mail trop court',
                    'max_length' => 'Mail trop long',
                    'valid_email' => 'Format mail incorrect.',
                ],
                'password' => [
                    'required'  => "Mode de passe vide!",
                    'min_length' => 'Mot de passe trop court',
                    'max_length' => 'Mot de passe trop long',
                ],
            ];
            // on restitue les informations
            $mail = $this->request->getVar('mail');
            session()->set('mail', $mail);
            $password = $this->request->getVar('password');
            session()->set('password', $password);


            if (!$this->validate($rules, $error)) {
                $data['validation'] = $this->validator;
            } else {
                $model = new UserModel();
                $user = null;
                try {
                    $user = $model->where('mail', $mail)->first();
                } catch (\CodeIgniter\Database\Exceptions\DatabaseException $ex) {
                    session()->setFlashdata('infos', 'Connexion impossible!');
                    return view('/Login/login', $data);
                } finally {

                    if (!$user) {
                        session()->setFlashdata('infos', 'Cet utilisateur n\'existe pas');
                    } else {
                        $pw = $this->request->getVar('password');
                        $pwh = $user['password'];

                        if (password_verify($pw, $pwh)) { // vérifie si password ok et dispatche
                            $data = $this->dispatch($user);
                            $route = $data['route'];
                            return view($route, $data);
                        }
                    }
                }
            }
        }
        return view('Login/login', $data);
    }

    /* fonction de redirection suivant profil utilisateur */
    private function dispatch($user)
    {
        helper(['form']);
        $user_info = new UserHelper();
        $user_info->setUserSession($user);
        $type = $user['type'];

        if ($user['image_url'] == null)
            $user['image_url'] = base_url() . "/assets/blank.png";

        $jobs = $user_info->getInfosCompany($user['id_user']);
        $skills = $user_info->getInfosCertificates($user['id_user']);

        $data = [
            "user" => $user,
            "jobs" => $jobs,
            "skills" => $skills,
            "route" => "User/profile_user",
            "type" => $type,
            "buttonColor" => getTheme($type, "button"),
            "headerColor" => getTheme($type, "header"),
            "gender" => getGender($user['gender']),
            "birthday" => dateFormat($user['birthday']),
            "title" => getTypeName($type),
        ];
        return $data;
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
        //$modelcp = new CompanyModel();
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
        // on part du principe une seule société par ville
        // une évolution future: vérifier l'adresse
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
                $company = $this->ifNotExistCompany($data_company);
                if ($company == null) { //la société n'existe pas on la rajoute
                    $this->saveCompany($data_user, $data_company, $kbis, $siret);
                } else { // la société existe donc on associe juste avec la table de jointure
                    $this->associateCompany($data_user, $company['id_company'], $kbis, $siret);
                }
                $data["title"] = "Login";
                return view('Login/login', $data);
            }
        }
    }

    /* profil utilisateur */
    public function profileuser()
    {
        helper(['form']);
        $user_info = new UserHelper();
        $user = $user_info->getUserSession();

        $data = [
            "title" => getTypeName($user['type']),
            "user" => $user,
            "buttonColor" => getTheme($user['type'], "button"),
        ];
        return view('User/profile_user.php', $data);
    }

    /* profil entreprise */
    public function profilecompany()
    {
        $id =  session()->get('id_user');
        $db      = \Config\Database::connect();
        $builder = $db->table('user');
        $builder->where('id_user', $id);
        $query   = $builder->get();
        $user = $query->getResultArray();
        $user = $user[0]; // juste le premier 

        $builder->select('company.name, company.address,company.city ,company.cp');
        $builder->join('user_has_company', 'user_has_company.id_user = user.id_user');
        $builder->join('company', 'user_has_company.id_company=company.id_company');
        $query = $builder->get();
        $infos = $query->getResultArray();
        $infos = $infos[0];

        $company = [
            "name" => $infos['name'],
            "address" => $infos['address'] . " " . $infos['city'] . ", " . $infos['cp']
        ];

        return $company;
    }

    /* mot de passe oublié */
    public function forgetpassword()
    {
        $data = [
            "title" => "Mot de passe oublié"
        ];
        return view('Login/forgetpassword.php', $data);
    }

    /* Deconnexion utilisateur */
    public function logout()
    {
        session()->destroy();
        return redirect()->to('/');
    }

    /* Inscription utilisateur*/
    public function signin()
    {
        $data = ["title" => "Inscription"];
        helper(['form']);
        $user_info = new UserHelper();
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
            ];
            $errorc = [
                'c_name' => ['required' => "Nom de la compagnie vide!"],
                'c_address' => ['required' => "Adresse de la compagnie vide!"],
                'c_city' => ['required' => "Ville de la compagnie vide!"],
                'c_cp' => ['required' => "Code postal de la compagnie vide!"],
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
                case "2": // Formateurs
                    if (!$this->validate($rulesf, $errorf)) {
                        $data['validation'] = $this->validator;
                        $sub = false;
                    } else {
                        $rights = '00 16 05 05 1D 1D 05';
                    }
                    break;
                case "3": // Entreprises
                    if (!$this->validate($rulesc, $errorc)) {
                        $data['validation'] = $this->validator;
                        $sub = false;
                    } else {
                        $rights = '00 16 05 05 1D 1D 05';
                    }
                    break;
                case "4": // Particuliers
                    $rights = '00 00 1C 05 05 05';
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
                    'rights' => $rights,
                    'phone' => $this->request->getVar('phone'),
                    'mail' => $this->request->getVar('mail'),
                    'password' => $this->request->getVar('password'),
                    'type' => $this->request->getVar('type'),
                    'newsletters' => $ischecked,
                ];

                if ($index == 4) { //Particulier
                    $newData['type'] = TYPE_USER;
                    $newData['status'] = 1;
                    $model->save($newData);
                }

                if ($index == 2) { // Formateur
                    $newData['type'] = TYPE_FORMER;
                    $newData['status'] = 1;
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
                if ($index == 3) { //Entreprise
                    $newData['type'] = TYPE_COMPANY;
                    $newData['status'] = 1;

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
                        $user_info->setCompanySession($newData, $newDatac);
                        return view("Login/confirmation", $data);
                    } else {
                        $company = $this->ifNotExistCompany($newDatac);
                        if ($company == null) {
                            $this->saveCompany($newData, $newDatac, $kbis, $siret);
                        } else {
                            $this->associateCompany($newData, $company['id_company'], $kbis, $siret);
                        }
                    }
                }
                session()->setFlashdata('success', 'Inscription réussie');
                $data["title"] = "Login";
                return view("Login/login", $data);
                die();
            }
        }
        return view('Login/signin', $data);
    }

    /* liste des factures suivant profil utilisateur */
    public function bill()
    {
        $user_helper = new UserHelper();
        $bill_helper = new BillHelper();

        $user = $user_helper->getUserSession();

        $type = $user['type'];
        $bills = [];
        switch ($type) {
            case USER:
            case COMPANY:
                $bills = $bill_helper->getFilterBill($user['id_user']);
                break;
            case FORMER:
                break;
            case ADMIN:
            case SUPER_ADMIN:
                $bills = $bill_helper->getFilterBill();
                break;
        }
        $data = [
            "title" => "Factures",
            "bills" => $bills,
            "user" => $user,
            "buttonColor" => getTheme($user['type'], "button"),
            "headerColor" => getTheme($user['type'], "header"),
        ];

        return view("Payment/bill.php", $data);
    }

    public function modif_contact()
    {
        helper(["form"]);
        $user_helper = new UserHelper();
        $user = $user_helper->getUserSession();

        if ($this->request->getMethod() == 'post') {
            $model = new UserModel();
            $updateData = [
                'name' => $this->request->getVar('name'),
                'firstname' => $this->request->getVar('firstname'),
                'address' => $this->request->getVar('address'),
                'city' => $this->request->getVar('city'),
                'cp' => $this->request->getVar('cp'),
                'country' => $this->request->getVar('country'),
                'phone' => $this->request->getVar('phone'),
                'mail' => $this->request->getVar('mail'),
                'birthday' => $this->request->getVar('birthday'),
                'gender' => $this->request->getVar('gender'),
            ];
            $model->update(session()->id_user, $updateData);
            $updateData['id_user'] = session()->id_user;
            $updateData['password'] = session()->password;
            $updateData['image_url'] = session()->image_url;
            $updateData['type'] = session()->type;
            $user_helper->setUserSession($updateData);
            session()->setFlashdata('success', 'Informations de contact modifiées');
        }
        $data = [
            "title" => "Informations de contact",
            "user" => $user,
            "buttonColor" => getTheme($user['type'], "button"),
        ];
        return view("User/modif_contact.php", $data);
    }

    public function modif_perso()
    {
        helper(["form"]);
        $user_helper = new UserHelper();
        $user = $user_helper->getUserSession();
        $data = [
            "title" => "Informations Personnelles",
            "user" => $user,
            "buttonColor" => getTheme($user['type'], "button"),
        ];
        if ($this->request->getMethod() == 'post') {
            return view("User/modif_contact.php", $data);
        }
    }

    public function modif_password()
    {
        helper(["form"]);
        $user_helper = new UserHelper();
        $user = $user_helper->getUserSession();
        $data = [
            "title" => "Modification mot de passe",
            "user" => $user,
            "buttonColor" => getTheme($user['type'], "button"),
        ];
        return view("User/modif_password.php", $data);
    }

    public function modif_skill()
    {
        helper(["form"]);
        // on récupère l'id de la session active
        $id = session()->get("id_user");
        // on récupère les informations utilisateur de la session active     
        $user_helper = new UserHelper();
        $user = $user_helper->getUserSession();
        $skills = $user_helper->getCertificates($id);

        if ($this->request->getMethod() == 'post') {

            $updateData = [
                'name' => $this->request->getVar('name'),
                'date' => $this->request->getVar('date'),
                'content' => $this->request->getVar('content'),
                'organism' => $this->request->getVar('organism'),
                'address' => $this->request->getVar('address'),
                'city' => $this->request->getVar('city'),
                'cp' => $this->request->getVar('cp'),
                'country' => $this->request->getVar('country'),
            ];

            $id_cetificate = $this->request->getVar('id_certificate');
             //on modifie la compétence dans la table certificate
            if ($id_cetificate) {
                $model = new CertificateModel();
                $model->update($id_cetificate, $updateData);
            }
            // on met à jour la liste des compétences
            $skills = $user_helper->getCertificates($id);
        }
        // on prépare les données pour la page html
        $data = [
            "title" => "Modification des compétences",
            "user" => $user,
            "buttonColor" => getTheme($user['type'], "button"),
            "headerColor" => getTheme($user['type'], "header"),
            "skills" => $skills,
        ];

        return view("User/modif_skill.php", $data);
    }

    public function delete_skill($id_skill)
    {
        // on récupère l'id de la session active
        $id = session()->get("id_user");
        $user_helper = new UserHelper();
        $user = $user_helper->getUserSession();
        //on supprime le certificat dans la table certificate
        $user_helper->removeCertificate($id_skill);
        // on met à jour la liste des compétences
        $skills = $user_helper->getCertificates($id);
        // on prépare les données pour la page html
        $data = [
            "title" => "Modification des compétences",
            "user" => $user,
            "buttonColor" => getTheme($user['type'], "button"),
            "headerColor" => getTheme($user['type'], "header"),
            "skills" => $skills,
        ];
        // on supprime en fonction de l'id utilisateur
        $user_has_certificate = new UserHasCertificateModel();
        $user_has_certificate->delete(['id_user', $id]);

        return view("User/modif_skill.php", $data);
    }

    public function add_skill()
    {
        // on récupère les informations utilisateur de la session active        
        $user_helper = new UserHelper();
        $user = $user_helper->getUserSession();

        if ($this->request->getMethod() == 'post') {

            $updateData = [
                'name' => $this->request->getVar('name'),
                'date' => $this->request->getVar('date'),
                'content' => $this->request->getVar('content'),
                'organism' => $this->request->getVar('organism'),
                'address' => $this->request->getVar('address'),
                'city' => $this->request->getVar('city'),
                'cp' => $this->request->getVar('cp'),
                'country' => $this->request->getVar('country'),
            ];
            // on ajoute la compétence dans la table certificate
            $model = new CertificateModel();
            $model->save($updateData);

            $user_has_certificate = new UserHasCertificateModel();
            // on enrichit la table intermédaire pour faire la jonction
            $id_certificate = $model->getInsertID();
            $data2 = [
                "id_user" => session()->get('id_user'),
                "id_certificate" => $id_certificate,
            ];
            $user_has_certificate->save($data2);
            // on informe visuelement de l'ajout     
            session()->setFlashdata('success', 'Compétence ajoutée!');
        }
        // on prépare les données pour la page html
        $data = [
            "title" => "Ajouter des compétences",
            "user" => $user,
            "buttonColor" => getTheme($user['type'], "button"),
            "headerColor" => getTheme($user['type'], "header"),
        ];

        return view("User/add_skill.php", $data);
    }
}
