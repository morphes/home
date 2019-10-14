<?php
$this->breadcrumbs=array(
        'Каталог товаров'=>array('#'),
        'Категории'=>array('index'),
        'Сортировка опций',
);
?>

<?php Yii::app()->clientScript->registerCoreScript('jQuery'); ?>

<?php Yii::app()->clientScript->registerScriptFile('/js/jquery-ui-1.8.22.custom.min.js'); ?>
<?php Yii::app()->clientScript->registerCssFile('/css/admin/jquery-ui.css'); ?>

<?php Yii::app()->clientScript->registerScriptFile('/js/backbone/underscore-min.js'); ?>
<?php Yii::app()->clientScript->registerScriptFile('/js/backbone/backbone-min.js'); ?>

<style>
    #options, #values {list-style-type: none; margin: 10px 0 0; padding: 0; width: 90%; }
    #options li {cursor: pointer; }
    #values li {cursor: move;}
    #options li, #values li {margin: 0 3px 3px 3px; padding: 0.4em; padding-left: 1.5em; font-size: 1.2em; height: 12px; }
    #options li span, #values li span { position: absolute; margin-left: -1.3em; }
</style>

<div id="loader">
    <img src="/img/admin/ajax-loader.gif">
</div>

<div id="page">
        <div class="row">
            <span class="span14">
                <div id="category">

                </div>
            </span>
        </div>

        <div class="row">
            <span class="span8">
                <?php echo CHtml::button('А-Я', array('class'=>'btn', 'id'=>'options-sort', 'style'=>'display:none;')); ?>
                <ul id="options"></ul>
            </span>
            <span class="span6">
                <?php echo CHtml::button('А-Я', array('class'=>'btn', 'id'=>'values-sort', 'style'=>'display:none;')); ?>
                <ul id="values"></ul>
            </span>
        </div>

        <div class="row">
                <span class="span14" id="actions" style="display: none;">
                    <div class="actions">
                        <?php echo CHtml::button('Сохранить', array('class'=>'btn primary', 'id'=>'save')); ?>
                        <?php echo CHtml::button('Отмена', array('class'=>'btn', 'id'=>'cancel', 'onclick'=>'history.back()')); ?>
                    </div>
                </span>
        </div>
</div>



<script>
    $(function(){

        var baseUrl = '/catalog2/admin/category/';

        // Модель категории
        var Category = Backbone.Model.extend({
            options: null,
            view: null,
            values_qt: 0,
            state: null,
            urlRoot: document.location.href,
            initialize: function() {
                var model = this;
                this.fetch({
                    success: function () {
                        // загрузка опций категории
                        model.options = new Options({model: Option});
                        model.options.fetch({
                            data: { category_id: model.get("id") },
                            success: function(){
                                // загрузка значений каждой опции
                                var options_len = model.options.models.length;
                                if(options_len == 0) {
                                    model.set({state: "loaded"});
                                }
                                model.options.each(function(option, index){
                                    option.values = new Values({model: Value});
                                    option.values.fetch({
                                        data: {option_id: option.get("id")},
                                        success: function(response) {
                                            model.values_qt+=response.length;
                                            if(index == options_len - 1) {
                                                // отметка категории как загруженной, запуск рендеринга
                                                model.set({state: "loaded"});
                                            }
                                        }
                                    });
                                });
                            }
                        });
                    }
                });
            }
        });
        // Модель опции
        var Option = Backbone.Model.extend({
            values: null
        });
        // Модель значения
        var Value = Backbone.Model.extend({
        });



        // Коллекция опций
        var Options = Backbone.Collection.extend({
            url: baseUrl + 'sortApi/class/Options',
            comparator: function(model) {
                return model.get('position');
            }
        });
        // Коллекция значений
        var Values = Backbone.Collection.extend({
            url: baseUrl + 'sortApi/class/Values',
            comparator: function(model) {
                return model.get('position');
            }
        });



        // представление категории
        var View = Backbone.View.extend({
            el_category: $("#category"),
            el_options: $("#options"),
            el_values: $("#values"),
            el_actions: $("#actions"),
            el_loader: $("#loader"),
            el_page: $("#page"),
            options: null,
            initialize: function() {
                var model = this.model;
                var view = this;
                model.bind("change:state", function () {
                    if (model.get("state") == "loaded")
                        view.render();
                });
            },
            templates: {
                "category": _.template($("#category-header").html()),
                "option": _.template($("#option-item").html()),
                "value": _.template($("#value-item").html())
            },
            // рендеринг страницы
            render: function () {
                this.el_loader.hide();
                this.el_page.show();
                // рендер заголовка страницы
                this.el_category.html(this.templates["category"](this.model.toJSON()));
                this.renderOptions();
                this.renderActions();
            },
            // рендер списка опций
            renderOptions: function() {
                var view = this;
                this.el_options.html("");
                this.model.options.each(function(option){
                    view.el_options.append(view.templates["option"](option.toJSON()));
                });
                // включение Sortable с сохранением позиций после сортировки
                this.el_options.sortable({
                    stop: function(){
                        $(this).children({}).each(function(index, obj){
                            view.model.options.get($(obj).attr("oid")).set({"position":index});
                        });
                    }
                });
                this.el_options.disableSelection();
                view.el_options.find('li').click(function(){
                    $("#values-sort").hide();
                    var option = view.model.options.get($(this).attr('oid'));
                    view.renderValues(option);
                    // подсветка кликнутой опции
                    view.el_options.find('li').each(function(index, obj) {
                        if($(obj).hasClass('ui-state-highlight'))
                                $(obj).removeClass('ui-state-highlight').addClass('ui-state-default');
                    });
                    $(this).addClass('ui-state-highlight');
                });
            },
            // рендер значений
            renderValues: function(option) {
                var view = this;
                this.el_values.html("");

                if(option.values.length)
                    $("#values-sort").attr("oid", option.id).show();

                option.values.each(function(value){
                    view.el_values.append(view.templates["value"](value.toJSON()));
                });
                // включение Sortable с сохранением позиций после сортировки
                this.el_values.sortable({
                    stop: function() {
                        $(this).children({}).each(function(index, obj){
                            option.values.get($(obj).attr("vid")).set({"position": index});
                        });
                    }
                });
            },
            // рендер управляющих кнопок
            renderActions: function() {
                var view = this;
                this.el_actions.find("#save").click(function(){
                    $(window).scrollTop(0);
                    view.el_loader.show();
                    view.el_page.hide();
                    var options_len = view.model.options.length;
                    // сохранение опций
                    var values_qt = 0;
                    view.model.options.each(function(option, opt_index){
                        option.save({}, {
                            success: function() {
                                // сохранение значений опции
                                option.values.each(function(value, val_index){
                                    value.save({}, {
                                        success: function() {
                                            values_qt++;
                                            if(values_qt == view.model.values_qt) {
                                                view.submit();
                                            }
                                        },
                                        wait: true
                                    });
                                });
                            },
                            wait: true
                        });
                    });
                });
                // обработка нажатия на кнопку сортировки по опциям
                $("#options-sort").click(function(){
                    view.model.options.comparator = function(model) {return model.get('name');}
                    view.model.options.sort();
                    view.model.options.each(function(elem, index) {
                        elem.set({position: index});
                    });
                    view.model.options.comparator = function(model) {return model.get('position');}
                    view.renderOptions();
                });
                // обработка нажатия на кнопку сортировки по значениям
                $("#values-sort").click(function(){
                    var option = view.model.options.get($(this).attr('oid'));
                    option.values.comparator = function(model) {return model.get('value');}
                    option.values.sort();
                    option.values.each(function(elem, index) {
                        elem.set({position: index});
                    });
                    option.values.comparator = function(model) {return model.get('position');}
                    view.renderValues(option);
                });
                this.el_actions.show();
                $("#options-sort").show();
            },
            submit: function() {
                this.el_loader.hide();
                document.location.href = '/catalog2/admin/category/update/id/'+this.model.get("id");
            }
        });


        // контроллер
        var Controller = Backbone.Router.extend({
            routes: {
                "": "index",
                "!/": "index"
            },
            index: function () {
                var category = new Category();
                var view = new View({model: category});
            }
        });

        var controller = new Controller();
        Backbone.history.start();
    });
</script>

<script type="text/template" id="category-header">
    <h1>Сортировка опций категории #<%= id %> (<%= name %>) </h1>
</script>

<script type="text/template" id="option-item">
    <li class="ui-state-default option" oid="<%= id %>"><span class="ui-icon ui-icon-arrowthick-2-n-s"></span><%= name %></li>
</script>

<script type="text/template" id="value-item">
    <li class="ui-state-highlight value" vid="<%= id %>"><span class="ui-icon ui-icon-arrowthick-2-n-s"></span><%= value %></li>
</script>