# Trabalhando com Pic-up

## Utilização básica
No arquivo referente ao layout, deve se utilizar os comandos javaScript para instanciar o pic-up



```html
<div id="pic-up"></div>

<script>
  $('#pic-up').picup({
      name: 'imagem'
  });
</script>
``` 

É obrigatório definir o **name** para seu uso. Automaticamente será definido o *enctype* do seu formulário.


Após submeter o formulário, inicie a classe para realizar o upload
```php
$picUp = new Picup('imagem'); //A string 'imagem' refere-se ao mesmo valor informado no index 'name'
$imagens = $picUp->upload('uploadTest/sub1');
``` 

## Cortando imagens
Caso queria fazer um corte, poderá usar o metodo **cut()** da seguinte forma.

simplificando ficaria assim:
```php
$picUp = new Picup('imagem');
$imagens = $picUp->upload('uploadTest/sub1',  $picUp->cut(500, 500));
``` 

caso queria fazer mais de um corte:
```php
$picUp = new Picup('imagem');
$corte1 = $picUp->upload('uploadTest/sub1',  $picUp->cut(500, 500));
$corte2 = $picUp->upload('uploadTest/sub1/thumb',  $picUp->cut(100, 100));
```
 
O método **cut()** recebe 4 argumentos, sendo
* @param $widthFinal
* @param $heightFinal
* @param string $type
    * **c** = Corte, corta a imagem centralizada no tamanho escolhido 
    * **o** = Objetiva, redimencina para o tamanho final (adciona tranparencia para completar a medida menor, e converte para png)
    * **p** = Proporcional, mantendo a medida maior da imagem igual a medida menor da thumb
    * **r** = Relativo, a medida que estiver faltando é redimencionada para o valor relativo a original
    * **x** [Padrão] = maXimo, corta pelo tamanho escolhido sem alterar as proporções originais
* @param array $files
* @param string $posfix <br>
    Exemplo:  $picup->cut(200, 200, null, '__thumb'); <br>
    Exemplo 2: $picup->cut(400, 400, 'o');

## Método upload()
O método upload trabalha com dois argumentos sendo
* @param $folder
* @param null $files

onde **$folder**, é o caminho onde será feito o upload, **$files** é o array contendo as imagens, esse parametro pode ser **null** assim utilizando os arquivos originais
 
 ## Trabalhando com modificações no padrão
 
 Aqui veremos algumas modificações no padrão normal da execução do pic-up
  ```javascript
  $('#pic-up').picup({
     buttonText: 'Escolher imagem',
     name: 'imagem',
     addFunction: function (img, opt) {
         return '<div><div><img src="' + img + '" width="80" /></div>'
          + '<div><label>Nome</label> <input name="label['+(opt != undefined ? opt.id : "" )+']" value="'+(opt != undefined && opt.label != undefined ? opt.label : "" )+'" /></div>'
             + '</div>';
     },
     deleteImg: function (content) {
        $(content).hide();
     },
     content: [
                 {id: 32, img: "http://www.minhaurl.com/imagens/ec193cp84fzbww_1_26296.JPG", label: "teste 32"},
                 {id: 41, img: "http://www.minhaurl.com/imagens/ec193cp84fzbww_1_26296.JPG", label: "teste 41"},
                 {id: 33, img: 'http://www.minhaurl.com/imagens/ec193cp89ruoww_1_72676.jpg', label: "teste 33"}
             ]
   });
```

Perceba que temos novos parametros na utilização do pic-up, **buttonText**, **addFunction**, **deleteImg** e **content**.

**buttonText**, é o texto usado no botão de seleção das imagens

**addFunction**, é a função que gera a visualização basica das imagens, tanto já carregadas quando enviadas, é obrigatório o uso de dois argumentos em usa criação no exempo a cima tempos *img* e *opt*, o seu **return** sera exibido na tela para cada imagem carregada ou enviada.

**deleteImg**, é a função que realiza a visualização da deleção de uma imagem carregada ou enviada no caso, está ocultando seu conteudo, é importe que não seja removido o contet da imagem, pois isso implicara em problemas na execução, é necessario a utilização de um argumento, no exemplo *content*

**content**, é um objeto que monta as imagens carregadas, são necessários duas chaves para cada laço *id* e *img*, paramos além desse poderão ser utilizados na função **addFunction** que terão entrada pelo segundo argumento, como no exemplo *opt.label*

 ## Tratando inputs adicionais
 Todo input adicionar deverá passar pelo metodo **filterInput()** para um pré tratamento, como no exemplo tempo o input *label*
 
 ```php
 $_POST['label'] = $picUp->filterInput($_POST['label']);
 ```
 
 Fazendo dessa forma o retorno na variavel *$_POST['label']* seguindo o exemplo assima será:
  
 ```txt
 $_POST['label'] = array(
    'new' => array(0 => 'lorem ipsum', 1 => 'dolor')
    'original' => array( 32 => 'teste 32', 33 => 'teste 33', 41 => 'teste 41')
 );
 ```
 
Sendo que em **new => array()** temos os labels das novas imagens, e em **original => array()** as carregadas
 
 ## Apagando imagens carregadas
O pic-up retorna após o submit as imagens que foram marcadas para serem apagadas, elas são retornadas na variavel **$_POST['picup-delete-content']**, que terá um array com o *id* passado e um boleam **0** para não apagar e **1** para apagar

```txt
[picup-delete-content] => Array
        (
            [imagem] => Array
                (
                    [32] => 0
                    [41] => 1
                    [33] => 0
                )
```
