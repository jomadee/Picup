<?php
namespace Picup;

class Picup
{

    private $name, $files;
    private $deleted = array();
    private $type = ['png' => 'image/webp', 'webp' => 'image/webp', 'jpg' => 'image/jpg', 'jpeg' => 'image/jpg'];

    /**
     * Picup constructor.
     * @param $name
     */
    function __construct($name)
    {
        $this->name = $name;
        $this->filterFiles();
    }

    /**
     *
     */
    private function filterFiles()
    {
        $this->files[$this->name] = [];

        if (isset($_POST['picup-delete'][$this->name]) && isset($_FILES['picup-files']['error'][$this->name])) {

            ksort($_POST['picup-delete'][$this->name]);

            $deleteKeys = array_keys($_POST['picup-delete'][$this->name]);

            foreach ($deleteKeys as $k => $v) {
                if ($_POST['picup-delete'][$this->name][$v] == 0) {
                    if ($_FILES['picup-files']['error'][$this->name][$k] === 0) {

                        $file = file_get_contents($_FILES['picup-files']['tmp_name'][$this->name][$k]);
                        $file = base64_encode($file);


                        $this->files[$this->name][$v] = array(
                            'name' => $this->rename($_FILES['picup-files']['name'][$this->name][$k]),
                            'type' => $_FILES['picup-files']['type'][$this->name][$k],
                            'file' => $file,
                        );
                    }
                } else {
                    $this->deleted[$v] = $k;

                    unset($_POST['picup-delete'][$this->name][$v],
                        $_FILES['picup-files']['error'][$this->name][$k],
                        $_FILES['picup-files']['name'][$this->name][$k],
                        $_FILES['picup-files']['tmp_name'][$this->name][$k],
                        $_FILES['picup-files']['size'][$this->name][$k],
                        $_FILES['picup-files']['type'][$this->name][$k]
                    );
                }
            }
        }

        //$_POST['picup-delete'][$this->name] = array_values($_POST['picup-delete'][$this->name]);
        unset($_FILES['picup-files']['error'][$this->name],
            $_FILES['picup-files']['name'][$this->name],
            $_FILES['picup-files']['tmp_name'][$this->name],
            $_FILES['picup-files']['size'][$this->name],
            $_FILES['picup-files']['type'][$this->name]);
    }

    /**
     * @param array $input
     * @return array
     */
    public function filterInput($input = array())
    {

        $inputOriginal = array();
        $inputNew = array();
        if (isset($_POST['picup-delete-content'][$this->name]))
            foreach ($input as $k => $v) {
                if (array_key_exists($k, $_POST['picup-delete-content'][$this->name])) {
                    $inputOriginal[$k] = $v;
                    unset($input[$k]);
                }
            }

        ksort($input);

        foreach ($this->deleted as $k => $v)
            unset($input[$k]);

        return array('new' => $input, 'original' => $inputOriginal);
    }

    /**
     * @param $width
     *
     * @param $height
     *
     * @param string $type
     * "c" = Corte, corta a imagem centralizada no tamanho escolhido [Padrão]
     * "o" = Objetiva, redimencina para o tamanho final (adciona tranparencia para completar a medida menor, e converte para png)
     * "p" = Proporcional, mantendo a medida maior da imagem igual a medida menor da thumb
     * "r" = Relativo, a medida que estiver faltando é redimencionada para o valor relativo a original
     * "x" = maXimo, corta pelo tamanho escolhido sem alterar as proporções originais
     *
     * @param string $posfix
     *
     * Exemplo  $picup->cut(200, 200, null, '__thumb');
     * Exemplo2 $picup->cut(400, 400, 'o');
     * @param bool $renderOut
     * @return array
     */
    public function cut($width, $height, $type = 'p', $posfix = '', $renderOut = false)
    {
        if($renderOut !== false && !isset($this->type[$renderOut])){
            $renderOut = false;
        }

        $return = array();

        // Cria uma nova imagem a partir de um arquivo ou URL
        foreach ($this->files[$this->name] as $k => $image) {
            if($renderOut !== false) {
                $imgExt = $renderOut;
                $image['type'] = $this->type[$renderOut];
            } else {
                $imgExt = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));
            }

            $image['file'] = base64_decode($image['file']);

            try {
                $oriImg = imagecreatefromstring($image['file']);
            } catch(Exception $e){
                continue;
            }


            if ($imgExt == 'webp' || $imgExt == 'png' || $imgExt == 'gif') {
                imagealphablending($oriImg, false);
                imagesavealpha($oriImg, true);
            }

            $widthFinal = $width;
            $heightFinal = $height;

            $oriWid = ImagesX($oriImg);
            $oriHei = ImagesY($oriImg);

            $heightFinal = ($heightFinal < 1 ? 1 : $heightFinal);

            $basPro = $widthFinal / $heightFinal;
            $oriPro = $oriWid / $oriHei;

            $indRed = $heightFinal / $oriHei;

            $novLef = 0;
            $novTop = 0;

            $novWid = $widthFinal;
            $novHei = $heightFinal;

            switch ($type == 'x' && $heightFinal < $oriHei && $widthFinal < $oriWid ? 'c' : $type) {
                case 'c':
                case 'r':
                    if ($basPro > $oriPro)
                        $indRed = $widthFinal / $oriWid;

                    $novWid = $oriWid * $indRed;
                    $novHei = $oriHei * $indRed;

                    if ($type == 'r') {
                        $widthFinal = $novWid;
                        $heightFinal = $novHei;
                    } else {
                        $novLef = ($widthFinal - $novWid) / 2;
                        $novTop = ($heightFinal - $novHei) / 2;
                    }

                    break;

                case 'x':
                    if ($heightFinal > $oriHei && $widthFinal > $oriWid) {
                        $heightFinal = $oriHei;
                        $widthFinal = $oriWid;
                    } else {
                        if ($basPro < $oriPro) {
                            $heightFinal = $oriHei;

                        } else {
                            $widthFinal = $oriWid;

                        }
                    }

                    $novWid = $oriWid;
                    $novHei = $oriHei;

                    $novLef = ($widthFinal - $novWid) / 2;
                    $novTop = ($heightFinal - $novHei) / 2;

                    break;

                case 'o':
                    $imgExt = 'png';
                case 'p':
                    if ($basPro < $oriPro)
                        $indRed = $widthFinal / $oriWid;

                    $novWid = $oriWid * $indRed;
                    $novHei = $oriHei * $indRed;

                    if ($type == 'p') {
                        $widthFinal = $novWid;
                        $heightFinal = $novHei;
                    } else {
                        $novLef = ($widthFinal - $novWid) / 2;
                        $novTop = ($heightFinal - $novHei) / 2;
                    }

                    break;
            }


            $newImg = imagecreatetruecolor($widthFinal, $heightFinal);

            ob_start();
            switch ($imgExt) {
				case 'jpeg':			
                case 'jpg':
                    imagecopyresampled($newImg, $oriImg, $novLef, $novTop, 0, 0, $novWid, $novHei, $oriWid, $oriHei);

                    imagejpeg($newImg, null, 100);
                    break;

                case 'png':
                case 'gif':
                    imagealphablending($newImg, false);
                    $corTra = imagecolorallocatealpha($newImg, 0, 0, 0, 127);
                    imagefill($newImg, 0, 0, $corTra);
                    imagesavealpha($newImg, true);
                    imagealphablending($newImg, true);

                    imagecopyresampled($newImg, $oriImg, $novLef, $novTop, 0, 0, $novWid, $novHei, $oriWid, $oriHei);

                    if ($imgExt == 'png') {
                        imagepng($newImg, null);
                    } else {
                        imagegif($newImg, null);
                    }
                    break;

                case 'webp';
                    imagealphablending($newImg, false);
                    $corTra = imagecolorallocatealpha($newImg, 0, 0, 0, 127);
                    imagefill($newImg, 0, 0, $corTra);
                    imagesavealpha($newImg, true);
                    imagealphablending($newImg, true);

                    imagecopyresampled($newImg, $oriImg, $novLef, $novTop, 0, 0, $novWid, $novHei, $oriWid, $oriHei);
                    imagewebp($newImg, null, 80);
                    break;
            }
            imagedestroy($newImg);

            $image_data = ob_get_clean();
            $image_data = base64_encode($image_data);

            $image['name'] = explode('.', $image['name']);
            array_pop($image['name']);
            $image['name'] = implode('.', $image['name']);

            $return[$k] = array(
                'name' => $image['name'] . $posfix . '.' . $imgExt,
                'type' => $image['type'],
                'file' => $image_data,
            );

        }

        return $return;
    }

    /**
     * @param int $w
     * @param int $h
     */
    public function toWebp($w = 1000, $h = 1000){
        $imagens = self::cut($w, $h, 'p', '', 'webp');

        $this->files[$this->name] = $imagens;
    }

    /**
     * @param $texto
     * @return mixed|string
     */
    public function rename($texto)
    {
        $texto = mb_strtolower($texto); // muda tudo para minusculo
        $imgExt = pathinfo($texto, PATHINFO_EXTENSION);

        $texto = substr($texto, 0, strrpos($texto, '.'));

        $texto = preg_replace(
            array("/(á|à|ã|â|ä)/", "/(é|è|ê|ë)/", "/(í|ì|î|ï)/", "/(ó|ò|õ|ô|ö)/", "/(ú|ù|û|ü)/", "/(ñ)/", "/(ç)/", "/( )/"),
            array('a', 'e', 'i', 'o', 'u', 'n', 'c', '-'),
            $texto);

        $texto = preg_replace('/[^a-z0-9-_]/i', '', $texto);

        $texto = $texto . '_' . substr(md5(time()), rand(0, 20), 10) . '.' . $imgExt;

        return $texto;
    }


    /**
     * @param $folder
     * @param null $files
     * @return array
     */
    public function upload($folder, $files = null)
    {
        if (empty($files))
            $files = $this->files[$this->name];

        if (substr($folder, -1) != '/')
            $folder = $folder . '/';


        foreach ($files as $k => $imagem) {
            $imagem['file'] = base64_decode($imagem['file']);
            file_put_contents($folder . $imagem['name'], $imagem['file']);

            $return[$k] = array(
                'name' => $imagem['name'],
                'orginalName' => $this->files[$this->name][$k]['name']
            );
        }

        return $return;
    }

}