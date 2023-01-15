<!-- <?php require_once($_SERVER['DOCUMENT_ROOT'] . '/php/functions/util.php') ?> -->
<?= $this->extend('layouts/profil') ?>
<?= $this->section('content') ?>
<div class="modal " tabindex="-1" id="myModalName">
    <div class="modal-dialog">
        <form action="" method="POST">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Modification du nom</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_user" id="id_user" value="<?= set_value('id_user') ?>">
                    <div class="form-group">
                        <label for="name">Nom</label>
                        <input type="text" class="form-control mb-2" name="name" id="name">
                    </div>
                    <div class="form-group">
                        <label for="name">Prénom</label>
                        <input type="text" class="form-control mb-2" name="firstname" id="firstname">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" aria-label="Close">Annuler</button>
                    <button type="submit" class="btn <?= $buttonColor ?>">Modifier</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal " tabindex="-1" id="myModalContact">
    <div class="modal-dialog">
        <form action="" method="POST">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Informations de contact</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body row">
                    <input type="hidden" name="id_user" id="id_user" value="<?= set_value('id_user') ?>">
                    <div class="col-md-6">
                        <div class='form-floating mb-3'>
                            <input type="text" class="form-control" name="address" id="address" value="<?= set_value('address') ?>" placeholder='Adresse'>
                            <label for="name">Adresse</label>
                        </div>
                        <div class='form-floating mb-3'>
                            <input type="text" class="form-control" name="cp" id="cp" value="<?= set_value('cp') ?>" placeholder='Code postal'>
                            <label for="cp">CP</label>
                        </div>
                        <div class='form-floating mb-3'>
                            <input type="text" class="form-control" name="city" id="city" value="<?= set_value('city') ?>" placeholder='Ville'>
                            <label for="city">Ville</label>
                        </div>
                        <div class='form-floating mb-3'>
                            <input type="text" class="form-control" name="country" id="country" value="France" placeholder='Pays'>
                            <label for="country">Pays</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class='form-floating mb-3'>
                            <input type="text" class="form-control" name="mail" id="mail" value="<?= set_value('mail') ?>" placeholder='Mail'>
                            <label for="mail">Mail</label>
                        </div>
                        <div class='form-floating mb-3'>
                            <input type="text" class="form-control" name="site" id="site" value="<?= set_value('site') ?>" placeholder='Site'>
                            <label for="site">Site</label>
                        </div>
                        <div class='form-floating mb-3'>
                            <input type="text" class="form-control" name="phone" id="phone" value="<?= set_value('phone') ?>" placeholder='Téléphone'>
                            <label for="phone">Téléphone</label>
                        </div>
                        <div class='form-floating mb-3'>
                            <input type="date" class="form-control" name="birthday" id="birthday" value="<?= set_value('birthday') ?>" placeholder='Anniversaire'>
                            <label for="birthday">Anniversaire</label>
                        </div>
                        <div class="form-floating mb-3">
                            <select class='form-select' id='gender' name="gender" value="<?= set_value('gender') ?>" aria-label='Genre'>
                                <option value='0'>Madame</option>
                                <option value='1'>Monsieur</option>
                                <option value='Null' selected>Non renseigné</option>
                            </select>
                            <label for="gender">Genre</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" aria-label="Close">Annuler</button>
                    <button type="submit" class="btn <?= $buttonColor ?>">Modifier</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- boite modal snapshot -->
<div class="modal" tabindex="-1" id="myCamera">
    <div class="modal-dialog">
        <div class="modal-content" style="align-items:center">
            <div class="modal-header">
                <h5 class="modal-title">Caméra - prendre une photo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="my_camera" class="mb-2"></div>
                <div id="results"></div>
            </div>
            <div class="modal-footer mb-0">
                <button type=button class="btn btn-outline-primary" onClick="take_snapshot()">Prendre Image</button>
                <button type=button class="btn btn-outline-primary" onClick="save_snapshot()" data-bs-dismiss="modal">Sauver Image</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
            </div>
        </div>
    </div>
</div>
<!-- boite modal import -->
<div class="modal" tabindex="-1" id="myPicture">
    <div class="modal-dialog">
        <div class="modal-content" style="align-items:center">
            <div class="modal-header">
                <h5 class="modal-title">Avatar - Importer un fichier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <img id='output' style="height:100px; width:100px;">
            </div>
            <div class="modal-footer mb-0">
                <input type='file' accept='image/*' class="btn btn-outline-primary" onchange='openFile(event)'>
                <button type=button class="btn btn-outline-primary" onClick="save_snapshot()" data-bs-dismiss="modal">Sauver Image</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
            </div>
        </div>
    </div>
</div>

<div class="container mt-3">
    <div class="row ">
        <div class="col-12 col-md-4 mb-4">
            <div id="frame_snapshot" class="row mb-2">
                <div id="snapshot" class="contain">
                    <img src='<?= $user['image_url'] ?>' style="width: 200px;" alt="Avatar" />
                </div>
            </div>
            <div>
                <a href="" class="btn btn-outline-primary me-2" data-bs-target="#myCamera" data-bs-toggle="modal">Caméra</a>
                <a href="" class="btn btn-outline-primary" data-bs-target="#myPicture" data-bs-toggle="modal">Importer</a>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="row">
                <div class="title1">
                    <div class="flex-between mb-4">
                        <span><?= $user['firstname'] . " " . $user['name'] ?> </span>
                        <span class="title2"><i class="bi bi-geo-alt" style="width:14px"></i><?= "  " . $user['city'] . ", " . $user['country'] ?></span>
                        <div><button class="btn-title0 mt-1" data-bs-toggle="modal" data-bs-target="#myModalName">Modifier</button></div>
                    </div>
                </div>
                <div class="mb-4 title0"><span><i class="bi bi-chat-left-fill "></i> Envoyer un message</span></div>
                <div class="mb-1 title0">
                    <div class="title0 flex">
                        <div class="start"><i class="bi bi-person-fill"></i>&nbsp;Informations de contact</div>
                        <div><button class="btn-title0 mt-1 " data-bs-toggle="modal" data-bs-target="#myModalContact">Modifier</button></div>
                    </div>
                    <hr class="fade-1">
                </div>
                <div>
                    <span class="title2 mt-1">Téléphone : <span class="ton-blue-5"><?= $user['phone'] ?></span></span>
                </div>
                <div>
                    <span class="title2 mt-1">Adresse : <span><?= $user['address'] . "<br>" . $user['cp'] . " " .
                                                                    $user['city'] ?></span></span>
                </div>
                <div>
                    <span class="title2 mt-1">Mail : <span class="ton-blue-5"><?= $user['mail'] ?></span></span>
                </div>
                <div>
                    <span class="title2 mt-1">Site : <span class="ton-blue-5">www.maformation.com</span></span>
                </div>
                <div class="mt-4 mb-1 title0">
                    <div class="title0 flex">
                        <div class="start"><i class="bi bi-person-fill"></i>&nbsp;Informations personnelles</div>
                    </div>
                    <hr class="fade-2">
                </div>
                <div>
                    <span class="title2 mt-1">Anniversaire : <span class="ton-blue-5"><?= dateFormat($user['birthday']) ?></span></span>
                </div>
                <div>
                    <span class="title2 mt-1">Genre : <span class="ton-blue-5"><?= ($user['gender'] == 0) ? "Féminin" : "Masculin" ?></span></span>
                </div>
            </div>
        </div>
    </div>
    <?= $this->endSection() ?>

    <?= $this->section('js') ?>
    <script type="text/javascript" src="<?= base_url() ?>/webcamjs/webcam.min.js"></script>
    <script>
        const snapshot = document.getElementById('snapshot');
        // Configure a few settings and attach camera
        Webcam.set({
            width: 150,
            height: 112.5,
            image_format: 'jpeg',
            jpeg_quality: 95
        });
        Webcam.attach('#my_camera');
        // preload shutter audio clip
        var shutter = new Audio();
        shutter.autoplay = false;
        shutter.src = "<?= base_url() ?>/webcamjs/shutter.ogg";
        var img_snap = "";

        function take_snapshot() {
            // play sound effect
            shutter.play();
            // take snapshot and get image data
            Webcam.snap(function(data_uri) {
                // display results in page
                //img_snap = data_uri;
                document.getElementById('results').innerHTML =
                    '<img style="width:150px" src="' + data_uri + '"/>';
                img_snap = data_uri;
            });
        }

        function save_snapshot() {
            snapshot.innerHTML = '<img style="width:200px" src="' + img_snap + '"/>';
        }

        var openFile = function(file) {
            var input = file.target;
            var reader = new FileReader();
            reader.onload = function() {
                var dataURL = reader.result;
                var output = document.getElementById('output');
                output.src = dataURL;
                img_snap = dataURL;
            };
            reader.readAsDataURL(input.files[0]);
        }
    </script>

    <?= $this->endSection() ?>