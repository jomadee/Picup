
;(function($){

    var instancesIdx = 0;
    var instances = [];

    //parametros default
    var sDefaults = {
        buttonText: 'Selecionar Fotos',
        onLoad: function(){
            return true;
        },
        addFunction: function contentImg(img, complement){
            return '<span style="display: block; background: url('+img+') center center no-repeat; background-size: cover; height: 80px; width: 80px" /></span>';
        },
        deleteImg: function (content) {
            $(content).hide();
        },
        messageReturn: function(message, nivel, data){
            return message;
        },
        deleteClass: "",
        multiplePhotos: true,
        messageClass: "",
        deleteButtonClass: "",
        contentClass: "",
        blockClass: "",
        maxSize: 4,
        content: null
    };

    var picupConstructor = function (context, opt) {
        opt = opt || {};

        instancesIdx++;
        instances[instancesIdx] = this;
        //var opt = $.extend(sDefaults, parametros);
        $(context).data('picupIndex', instancesIdx);

        /**
         * determina o valor de uma opção
         * @param optIndex
         * @param _opt
         */
        this.setOpt = function (optIndex, _opt) {
            opt[optIndex] = _opt;
            this.refresh();
        };

        this.refresh = function () {
            var $context = $(context);
            $context.html('');
            construct.apply($context, []);
        };

        function construct() {

            $(this).closest('form').attr({'enctype': 'multipart/form-data'});
            $(this).addClass('pic-up-imagens');

            if(opt.name == undefined) {
                $(this).html('pic-up: error');
                console.error('Atributo name não foi definido ao pic-up');
                return false;
            }

            const inputFile = $('<input>').attr({
                                    type: "file",
                                    name: 'picup-files[' + opt.name + '][]',
                                    accept: "image/*",
                                    "data-active": "true"
                                }).css({
                                    display: "none"
                                });

            if(opt.multiplePhotos) {
                inputFile.attr('multiple', 'true')
            }

            const picupButton = $('<div>').addClass('picup-button').html('<button type="button">'+opt.buttonText+'</button>');
            const picupUplods = $('<div>').addClass('picup-uploads');

            var html = $('<div>').addClass('picup-block '+opt.blockClass).append(picupButton).append(picupUplods);

            $(this).html(html);

            opt.onLoad();

            picupButton.children('button').click(function(){

                if($(html).find('input[type="file"][data-active="true"]').length == 0) {
                     $(this).before(inputFile.clone());
                }

                $(html).find('input[type="file"][data-active="true"]').click();
            });

            /** carregamento do conteudo já existente **/
            if(opt.content != null){
                for(var vlr in opt.content){
                    if(opt.content[vlr].id == undefined) {
                        console.error('Campo de id no content não foi encontrado');
                        continue;
                    }

                    (function(){
                        const content = $('<div>').addClass('picup-content ' +opt.contentClass);

                        if(opt.multiplePhotos) {
                            var inputDelete = $('<input type="text" value="0" name="picup-delete-content[' + opt.name + '][' + opt.content[vlr].id + ']" style="display: none;"/>');

                            var buttonDelete = $('<button>', {
                                type: 'button',
                                class: opt.deleteButtonClass
                            }).text('Apagar').click(function () {
                                $(inputDelete).val('1');
                                opt.deleteImg(content);
                            });

                            $(content).append($('<div>').addClass('picup-delete ' + opt.deleteClass).append([inputDelete, buttonDelete]));
                        }

                        content.prepend(opt.addFunction(opt.content[vlr].img, opt.content[vlr]));

                        $(html).find('.picup-uploads').append(content);
                    })()
                }

            }

            var idx = 0;

            $(html).on('change', 'input[type="file"][data-active="true"]', function(event){

                if(opt.multiplePhotos) {
                    $(this).attr({'data-active': false});
                } else {
                    $('.picup-message').html(" ");
                    $('.picup-content').remove();
                }

                $.each(this.files, function(index, file){

                    if(file.type.match('image.*')){

                        (function(idx){
                            var reader = new FileReader();

                            reader.onload = function(f){

                                var returnMessage = "";
                                
                                var content = $('<div>')
                                    .attr({'data-idx': 'pc_'+idx})
                                    .addClass('picup-content ' + opt.contentClass);
                                
                                var size = ((f.total/1024)/1024); // em MB
                                size = parseFloat(size.toFixed(3));

                                content.append(opt.addFunction(f.target.result, {id: 'pc_'+idx}));

                                const inputDelete = $('<input>')
                                    .attr({type: "hidden", name: 'picup-delete[' + opt.name + '][' + 'pc_' + idx + ']' })
                                    .val('0');

                                const buttonDelete = $('<button>', {
                                    type: 'button',
                                    class: opt.deleteButtonClass
                                }).text('Apagar').click(function () {
                                    inputDelete.val('1');
                                    opt.deleteImg(content);
                                });

                                if(size > opt.maxSize){
                                    if(opt.multiplePhotos) {
                                        inputDelete.val('1');
                                    }

                                    returnMessage = opt.messageReturn("A imagem não pode ultrapassar "+opt.maxSize+"MB. Reduza a imagem e tente novamente.", "1", {"size": size})
                                }

                                if(returnMessage != '') {
                                    content.append('<div class="picup-message ' + opt.messageClass + '"></div>');
                                    content.find('.picup-message').append(returnMessage);
                                }

                                content.append('<div class="picup-delete ' + opt.deleteClass + '"></div>');
                                content.find('.picup-delete').append([inputDelete, buttonDelete]);

                                $(html).find('.picup-uploads').append(content);
                            };

                            reader.readAsDataURL(file);
                        })(idx++)
                    }
                });
            });
        }
        
        construct.apply($(context), []);
    };

    $.fn.extend({
        picup: function () {

            var args = arguments;
            var opt = $.extend({}, sDefaults, args[0]);

            return this.each(function(){

                var $context = $(this);

                if (args.length == 1) {
                    new picupConstructor($context, opt);
                } else if (args.length == 2) {
                    if ($context.is('.pic-up-imagens')) {
                        instances[$context.data('picupIndex')].setOpt(args[0], args[1]);
                    }
                } else if (args.length == 0) {
                    if ($context.is('.pic-up-imagens')) {
                        instances[$context.data('picupIndex')].refresh();
                    }
                }

            });
        }
    });

})(jQuery);
