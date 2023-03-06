<?php

namespace App\Controllers;

use Predis\Command\Redis\SUBSTR;

// Le 05/02/2023


class DashBoard extends BaseController
{

    /**
     * listformers
     * Liste des formateurs (profil)
     * @return void
     */
    public function listformers()
    {
        $title = "Liste des formateurs";
        $public = $this->user_model->getFormers();
        $builder = $public['builder'];
        $formers = $public['formers'];
        $listformers = [];
        // on map la table formateur
        foreach ($formers as $former) {
            $listformers[] = [
                "id_user" => $former['id_user'],
                "name" => $former['name'],
                "firstname" => $former['firstname'],
                "address" => $former['address'],
                "city" => $former['city'],
                "cp" => $former['cp'],
                "country" => $former['country'],
                "mail" => $former['mail'],
                "phone" => $former['phone'],
            ];
        }
        /* compétences certificats*/
        $builder->select('certificate.name,certificate.content,certificate.date,certificate.organism,certificate.address,certificate.city,certificate.cp,certificate.country');
        $skills = [];

        for ($i = 0; $i < count($listformers); $i++) {
            $builder->where('user.id_user', $listformers[$i]['id_user']);
            $builder->join('user_has_certificate', 'user_has_certificate.id_user = user.id_user');
            $builder->join('certificate', 'user_has_certificate.id_certificate = certificate.id_certificate');

            $query = $builder->get();
            $certificates = $query->getResultArray();

            $certi = [];
            foreach ($certificates as $certificate) {
                $certi[] = [
                    "name" => $certificate['name'],
                    "content" => $certificate['content'],
                    "date" => $certificate['date'],
                    "organism" => $certificate['organism'],
                    "address" => $certificate['address'],
                    "city" => $certificate['city'],
                    "cp" => $certificate['cp'],
                    "country" => $certificate['country'],
                ];
            }
            $listformers[$i]["skills"] = $certi;
        }
        $builder->select('company.name, company.address,company.city ,company.cp,company.country');
        $builder->join('user_has_company', 'user_has_company.id_user = user.id_user');
        $builder->join('company', 'user_has_company.id_company=company.id_company');
        $query = $builder->get();
        $companies = $query->getResultArray();

        $jobs = [];
        foreach ($companies as $company) {
            $jobs[] = [
                "name" => $company['name'],
                "address" => $company['address'],
                "city" => $company['city'],
                "cp" => $company['cp'],
                "country" => $company['country'],
            ];
        }

        $user = $this->user_model->getUserSession();

        $former_json = json_encode($listformers);
        file_put_contents("former.json", $former_json);

        $data = [
            "title" => $title,
            "former_json" => base_url() . "/former.json",
            "jobs" => $jobs,
            "user" => $user,
            "headerColor" => getTheme($user['type'], "header"),
            "headerExtraColor" => getTheme($user['type'], "header_extra"),
            "buttonColor" => getTheme($user['type'], "button"),
        ];

        return view('Admin/list_former_admin.php', $data);
    }

    /**
     * privileges
     * Gestions des droits (profil)
     * @return void
     */
    public function privileges()
    {

        $title = "Liste des privilèges";
        $db      = \Config\Database::connect();
        $builder = $db->table('user');
        $builder->where('type', ADMIN);
        $builder->orWhere('type', SUPER_ADMIN);
        $query   = $builder->get();
        $formers = $query->getResultArray();
        $listformers = [];

        foreach ($formers as $former) {
            $listformers[] = [
                "id_user" => $former['id_user'],
                "name" => $former['name'],
                "firstname" => $former['firstname'],
                "address" => $former['address'],
                "city" => $former['city'],
                "cp" => $former['cp'],
                "country" => $former['country'],
                "mail" => $former['mail'],
                "phone" => $former['phone'],
                "type" => $former['type'],
                "rights" => $former['rights'],
            ];
        }
        /* compétences certificats*/
        $builder->select('certificate.name,certificate.content,certificate.date,certificate.organism,certificate.address,certificate.city,certificate.cp,certificate.country');
        

        for ($i = 0; $i < count($listformers); $i++) {
            $builder->where('user.id_user', $listformers[$i]['id_user']);
            $builder->join('user_has_certificate', 'user_has_certificate.id_user = user.id_user');
            $builder->join('certificate', 'user_has_certificate.id_certificate = certificate.id_certificate');
            $query = $builder->get();
            $certificates = $query->getResultArray();

            $certi = [];
            foreach ($certificates as $certificate) {
                $certi[] = [
                    "name" => $certificate['name'],
                    "content" => $certificate['content'],
                    "date" => $certificate['date'],
                    "organism" => $certificate['organism'],
                    "address" => $certificate['address'],
                    "city" => $certificate['city'],
                    "cp" => $certificate['cp'],
                    "country" => $certificate['country'],
                ];
            }
            $listformers[$i]["skills"] = $certi;
        }
        //
        $builder->select('company.name, company.address,company.city ,company.cp,company.country');
        $builder->join('user_has_company', 'user_has_company.id_user = user.id_user');
        $builder->join('company', 'user_has_company.id_company=company.id_company');
        $query = $builder->get();
        $companies = $query->getResultArray();
        //
        $jobs = [];
        foreach ($companies as $company) {
            $jobs[] = [
                "name" => $company['name'],
                "address" => $company['address'],
                "city" => $company['city'],
                "cp" => $company['cp'],
                "country" => $company['country'],
            ];
        }
        $user = $this->user_model->getUserSession();
        $right_json = json_encode($listformers);    
        file_put_contents("right.json", $right_json);        
        //
        $data = [
            "title" => $title,
            "listformers" => $listformers,
            "right_json"=>base_url()."/right.json",
            "jobs" => $jobs,
            "user" => $user,
            "type=" => $user['type'],
            "headerColor" => getTheme(session()->type, "header"),
        ];
        return view('Admin/list_privileges.php', $data);
    }


    /**
     * listarticles
     * Liste des articles de l'utilisateur (profil admin et former)
     * @return void
     */
    public function listarticles()
    {
        $title = "Liste des articles";
        $user = $this->user_model->getUserSession();
        $public = $this->user_model->getFilterUser();

        $builder = $public['builder'];
        $articles = $this->article_model->getArticlesbyAuthor($builder, $user['id_user']);

        $listarticles = [];
        $listarticles = $this->article_model->MapArticles($listarticles, $articles);
        // Fonction select remplit les options
        $tag = $this->article_model->triTagArticle($listarticles);
        $articles_json = json_encode($listarticles);
        file_put_contents("article.json", $articles_json);

        $data = [
            "title" => $title,
            "listarticles" => $listarticles,
            "article_json" => base_url() . "/article.json",
            "user" => $user,
            "type" => session()->type,
            "headerColor" => getTheme(session()->type, "header"),
            "tag"=>$tag,
        ];

        return view('Articles/list_article_user.php', $data);
    }


    /**
     * listpublishes
     * Liste des publications de l'utilisateur (profil admin et former)
     * @return void
     */
    public function listpublishes()
    {
        $listpublishes = [];
        $title = "Liste des publications";
        $user = $this->user_model->getUserSession();
        $publishes = $this->publication_model->getPublishesbyAuthor($user['id_user']);
        //
        $listpublishes = $this->publication_model->MapPublishes($listpublishes, $publishes);
        //
        $listpublishes = $this->publication_model->getFilterPublishesArticles($listpublishes, $user['id_user']);
        //
        $publishe_json = json_encode($listpublishes);
        file_put_contents("publishe.json", $publishe_json);

        $data = [
            "title" => $title,
            "publishe_json" => base_url() . "/publishe.json",
            "user" => $user,
            "headerColor" => getTheme(session()->type, "header"),
            "buttonColor" => getTheme(session()->type, "button"),
        ];
        return view('Publishes/list_publishes_user.php', $data);
    }

    /**
     * dashboard_article
     * Tableau de bord des articles (profil admin)
     * @return void
     */
    public function dashboard_article()
    {
        $listarticles = [];
        $title = "Tableau des articles";
        $user = $this->user_model->getUserSession();
        $public = $this->article_model->getArticles();
        $builder = $public['builder'];
        $articles = $public['articles'];
        //
        $listarticles = $this->article_model->MapArticles($listarticles, $articles);
        $listarticles = $this->article_model->getAuthorsArticles($listarticles, $builder);
        $article_json = json_encode($listarticles);
        //
        file_put_contents("dashboard_article.json", $article_json);
        $data = [
            "title" => $title,
            //"articles" => $listarticles,
            "dashboard_article_json" => base_url() . "/dashboard_article.json",
            "user" => $user,
            "type" => session()->type,
            "headerColor" => getTheme(session()->type, "header"),
            "buttonColor" => getTheme(session()->type, "button"),
        ];
        return view('Admin/dashboard_article_admin.php', $data);
    }

    /**
     * dashboard_publishes
     * Tableau de bord des publications (profil admin)
     * @return void
     */
    public function dashboard_publishes()
    {
        $listpublishes = [];
        $title = "Tableau des publications";
        $user = $this->user_model->getUserSession();
        //
        $publishes = $this->publication_model->getFilterPublishes();
        //
        $listpublishes = $this->publication_model->MapPublishes($listpublishes, $publishes);
        //
        $listpublishes = $this->publication_model->getAuthorsPublishes($listpublishes);
        //
        $listpublishes = $this->publication_model->getFilterPublishesArticles($listpublishes, 0);
        $publishe_json = json_encode($listpublishes);
        //
        file_put_contents("dashboard_publication.json", $publishe_json);
        $data = [
            "title" => $title,
            "publishes" => $listpublishes,
            "dashboard_publication_json" => base_url() . "/dashboard_publication.json",
            "user" => $user,
            "buttonColor" => getTheme(session()->type, "button"),
            "headerColor" => getTheme(session()->type, "header"),
        ];
        return view('Admin/dashboard_publishes_admin.php', $data);
    }

    /**
     * previewarticle
     * Aperçu des articles (profil admin et former)
     * @return void
     */
    public function previewarticle()
    {
        $title = "Aperçu de l'article";
        if ($this->isPost()) {

            $id = $this->request->getVar('id_article');
            $public = $this->article_model->getArticlesbyId($id);
            $article = $public['article'];

            $user = $this->user_model->getUserSession();

            $article['image_url'] = defaultImage($article, 'DEFAULT_IMG_ARTICLES');

            $data = [
                "title" => $title,
                "article" => $article,
                "user" => $user,
                "buttonColor" => getTheme(session()->type, "button"),
            ];
            return view('Articles/preview_article.php', $data);
        }
    }


    /**
     * previewpublish
     * Aperçu des publications (profil admin et former)
     * @return void
     */
    public function previewpublish()
    {
        $title = "Aperçu de la publication";

        if ($this->isPost()) {

            $id = $this->request->getVar('id_publication');
            $publication = $this->publication_model->getPublisheById($id);
            $user = $this->user_model->getUserSession();

            $user['image_url'] = defaultImage($user, 'DEFAULT_IMG_BLANK');
            $publication['image_url'] = defaultImage($publication, 'DEFAULT_IMG_PUBLISHES');

            $data = [
                "title" => $title,
                "publication" => $publication,
                "user" => $user,
                "buttonColor" => getTheme(session()->type, "button"),
            ];
            return view('Publishes/preview_publish.php', $data);
        }
    }

    /**
     * dashboard_media
     * Tableau de bord média livres et vidéos (profil admin)
     * @param  mixed $type
     * @return void
     */

    private function refresh_media($type)
    {
        switch ($type) {
            case VIDEO:
                $title = "Liste des vidéos";
                $public = $this->media_model->getVideos();
                $builder = $public['builder'];
                $medias = $public['videos'];
                $media = "video";
                break;
            case BOOK:
                $title = "Liste des livres";
                $public = $this->media_model->getBooks();
                $builder = $public['builder'];
                $medias = $public['books'];
                $media = "book";
                break;
        }

        $listmedias = [];
        $listmedias = $this->media_model->MapMedias($listmedias, $medias);
        $listmedias = $this->media_model->getAuthorsMedias($listmedias, $builder);
        $media_json = json_encode($listmedias);

        //
        file_put_contents($media . ".json", $media_json);

        $user = $this->user_model->getUserSession();
        $data = [
            "title" => $title,
            "listmedias" => $listmedias,
            "media_json" => base_url() . "/" . $media . ".json",
            "user" => $user,
            "type" => session()->type,
            "typeofmedia" => $type,
            "headerColor" => getTheme(session()->type, "header"),
            "buttonColor" => getTheme(session()->type, "button"),
        ];
        return view('Admin/dashboard_media_admin.php', $data);
    }
    
    public function dashboard_media($type)
    {
        return $this->refresh_media($type);
    }

    public function status_media($type)
    {
        //
        if ($this->isPost()) {
            //
            $id_media = $this->getVar("id_media");
            $status = $this->getVar("status");
            //
            $dataUpdate = [
                "id_media" => $id_media,
                "status" => $status,
            ];            
            $this->media_model->save($dataUpdate);
            //
            return $this->refresh_media($type);            
        }
    }
    /**
     * listmedias
     * Liste de tous les médias livres et vidéos associé au profil 
     * (profil admin et former)
     * @param  mixed $type
     * @return void
     */
    public function listmedias($type)
    {
        // on récupère la sessions associé à cet utilisateur
        $session = $this->user_model->getUserSession();
        // on récupère la requete pour user
        $public = $this->user_model->getFilterUser();
        //
        switch ($type) {
            case VIDEO:
                $title = "Liste des vidéos";
                $media = "video.json";
                break;
            case BOOK:
                $title = "Liste des livres";
                $media = "book.json";
                break;
        }
        $builder = $public['builder'];
        $medias = $this->media_model->getAuthorMedias($session, $builder, $type);
        $listmedias = [];
        //
        switch (session()->type) {
            case ADMIN:
            case SUPER_ADMIN:
                $listmedias = $this->media_model->ValidatedMedias($type);
                break;
            case FORMER:
            case USER:
            case COMPANY:
                $listmedias = $this->media_model->MapMedias($listmedias, $medias);
                break;
        }

        for ($i = 0; $i < count($listmedias); $i++) {
            if ($type == BOOK)
                $listmedias[$i]['image_url'] = defaultImage($listmedias[$i], 'DEFAULT_IMG_BOOK');
            if ($type == VIDEO)
                $listmedias[$i]['image_url'] = defaultImage($listmedias[$i], 'DEFAULT_IMG_VIDEO');
        }
        // récupère les tags associés au média
        $listmedias = $this->media_model->getTagName($listmedias);
        // Liste select pour effectuer le tri
        $authors = $this->media_model->triAuthorMedia($listmedias);
        $tag = $this->media_model->triTagMedia($listmedias); 
        //
        $media_json = json_encode($listmedias);
        file_put_contents($media, $media_json);
        //
        $data = [
            "title" => $title,
            "media_json" => base_url() . "/" . $media,
            "user" => $session,
            "type" => session()->type,
            "headerColor" => getTheme(session()->type, "header"),
            "buttonColor" => getTheme(session()->type, "button"),
            "authors"=>$authors,
            "tag"=>$tag,
        ];
        return view('Media/list_medias_user.php', $data);
    }

    /**
     * preview_training
     * Aperçu des formations (profil admin et former)
     * @return void
     */
    public function preview_training()
    {
        $title = "Gestion des pages";
        $user = $this->user_model->getUserSession();
        $listPages = [];
        $actions = "";
        $id_training = null;
        $title_training = "";
        if ($this->isPost()) {
            $id_training = $this->request->getVar('id_training');
            $title_training = $this->request->getVar('title');
            session()->id_training = $id_training;
            session()->title_training = $title_training;

            $pages = $this->training_model->getFilterPages($id_training);
            $actions = $this->request->getVar('actions');

            // map sur le tableau si nécessaire
            foreach ($pages as $page) {
                $listPages[] = [
                    "id_page" => $page['id_page'],
                    "title" => $page['title'],
                    "content" => $page['content'],
                    "image_url" => $page['image_url'],
                    "video_url" => $page['video_url'],
                ];
            }
        }
        $data = [
            "title" => $title,
            "pages" => $listPages,
            "user" => $user,
            "buttonColor" => getTheme(session()->type, "button"),
            "headerColor" => getTheme(session()->type, "header"),
            "id_training" => $id_training,
            "title_training" => $title_training,
            "modalDelete" => "",
        ];
        if ($actions === "list") {
            $data['title'] = "Liste des pages";
            return view('Training/page_list.php', $data);
            die();
        }
        return view('Admin/dashboard_page.php', $data);
    }

    public function preview_page()
    {
        $title = "Page ";
        $user = $this->user_model->getUserSession();
        $listPages = [];
        $actions = "";
        if ($this->isPost()) {
            $id_page = $this->request->getVar('id_page');
            $id_training = $this->request->getVar('id_training');

            $pages = $this->page_model->getPageById($id_page);

            // map sur le tableau si nécessaire
            foreach ($pages as $page) {

                $listPages[] = [
                    "id_page" => $page['id_page'],
                    "title" => $page['title'],
                    "content" => $page['content'],
                    "image_url" => $page['image_url'],
                    "video_url" => $page['video_url'],
                ];
            }
            $page = null;
            if (count($listPages) > 0) {
                $page = $listPages[0];
                session()->page_id = $page['id_page'];
                session()->title_page = $page['title'];
                session()->image_page = $page['image_url'];
                session()->content = $page['content'];
            }
        }
        $data = [
            "title" => $title,
            "page" => $page,
            "user" => $user,
            "buttonColor" => getTheme(session()->type, "button"),
            "headerColor" => getTheme(session()->type, "header"),
            "id_page" => $id_page,
            "id_training" => $id_training,
            "modalDelete" => "",
        ];

        return view('Training/page_view.php', $data);
    }
}
