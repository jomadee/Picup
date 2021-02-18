<?php
ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

header('Content-Type: text/html; charset=utf-8');
require '../vendor/autoload.php';

use Picup\Picup;


if(!empty($_POST)){
    $picUp = new Picup('imagem');

    $_POST['label'] = $picUp->filterInput($_POST['label']);
    $picUp->toWebp();

    $imagens = $picUp->upload('uploadTest', $picUp->cut(200, 200, 'p'));

    echo '<pre>' , print_r($imagens, true) , '</pre>';

    echo '<a href="">voltar</a>';
    die();
}

?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <script type="text/javascript" src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
        <script src="../js/pic-up.js"></script>
        <link  href="../css/pic-up.css" rel="stylesheet" type="text/css" />
    </head>

    <body>
        <form action="?post" method="post">
            <div id="pic-up"></div>
            <div id="pic-up2"></div>

            <hr>

            <button type="submit">Enviar</button>
        </form>

        <script>
            $('#pic-up').picup({
                name:'imagem',
                content: [
                    {id: 32, img: "https://upload.wikimedia.org/wikipedia/commons/thumb/1/12/Baobob_tree.jpg/800px-Baobob_tree.jpg", label: "teste 32"},
                    {id: 41, img: "https://upload.wikimedia.org/wikipedia/commons/5/52/Desert_Rose.JPG", label: "teste 41"},
                    {id: 33, img: 'https://upload.wikimedia.org/wikipedia/commons/thumb/a/a0/Orchis_militaris_flowers.jpg/567px-Orchis_militaris_flowers.jpg', label: "teste 33"}
                ],
                addFunction: function (img, opt) {
                    return '<div>'
                            +'<div><img src="' + img + '" width="80" /></div>'
                            +'<div>'
                                +'<label>Nome</label> '
                                +'<input name="label['+(opt != undefined ? opt.id : "" )+']" value="'+(opt != undefined && opt.label != undefined ? opt.label : "" )+'" />'
                                +'<input type="radio" name="principal" />'
                            +'</div>'
                        + '</div>';
                }
            });
        </script>
    </body>
</html>