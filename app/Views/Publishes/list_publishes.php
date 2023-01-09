<?php require_once($_SERVER['DOCUMENT_ROOT'] . '/php/functions/util.php') ?>
<?= $this->extend('layouts/default') ?>
<?= $this->section('content') ?>

<div class=" container mx-auto w-75">
    <h1 class="mt-2 mb-3"> <?= $title ?></h1>
    <div class="row align-items-center justify-content-center">
        <?php $i = 0;
        foreach ($listpublishes as $publishe) : ?>
            <form action="/publishes/list/details" method="post">
                <div class="card mb-2 flex-row ">
                    <img src=<?= $publishe['image_url'] ?> class="p-5 card-img-left" style="width: 33%;">
                    <div class="card-body">
                        <h5 class="card-title"><?= $publishe['subject'] ?></h5>
                        <?php $j = 0;
                        foreach ($publishe['user'] as $user) : ?>
                            <small><?= "Ecrit par " . $user['name'] . " " . $user['firstname'] . " le " . dateFormat($publishe['datetime']) ?></small>
                        <?php $j++;
                        endforeach ?>
                        <div class="mt-3">
                            <p class="card-description" style="height: 6rem;"><?= $publishe['description'] ?></p>
                        </div>
                        <input type="hidden" name="id_publication" value="<?= $publishe['id_publication'] ?>">
                        <button type="submit" class="btn btn-outline-primary mr-2 float-end">Voir Plus</button>
                    </div>
                </div>
            </form>
        <?php $i++;
        endforeach ?>
    </div>
</div>


<?= $this->endSection() ?>


<?= $this->section('js') ?>

<?= $this->endSection() ?>