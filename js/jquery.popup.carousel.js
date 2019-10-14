
(function ($) {
	$.fn.carousel = function (options) {
		var settings = {
			duration:300,
			direction:"horizontal",
			minimumDrag:20,
			thumbs:{},
			vertical:'top',
			beforeStart:function () {
			},
			afterStart:function () {
			},
			beforeStop:function () {
			},
			afterStop:function () {
			}
		};

		$.extend(settings, options || {});

		return this.each(function () {
			if (this.tagName.toLowerCase() != "ul") return;

			var originalList = $(this);
			var pages = originalList.children();
			var images = pages.find('img');
			var width = originalList.parent().width();
			var height = originalList.parent().height();
			var thumbs = settings.thumbs;
			//Css
			var containerCss = {position:"relative", overflow:"hidden", width:width, height:height};
			var listCss = {position:"relative", padding:"0", margin:"0", listStyle:"none", width:pages.length * width};
			var listItemCss = {width:width, height:height};

			var imagesCss = {'max-width':width, 'max-height':height};
			var container = $("<div>").css(containerCss);
			var list = $("<ul>").css(listCss);
			var currentPage = 0, start, stop;
			var self = this;

			this.slideTo = function(setPage){
				currentPage = setPage;
				var new_width = -1 * width * setPage;

				list.animate({ left:new_width}, settings.duration,function(){
					list.find('li').removeClass('active').eq(setPage).addClass('active');
					thumbs.not(thumbs.eq(currentPage).addClass('active')).removeClass('active');
					settings.afterStop.apply(list, arguments);
				});
			};

			this.getCurPage = function(){
				return currentPage;
			};

			this.setCurPage = function(page){
				currentPage = page;
			};

			/*функция, подменяющая атрибут src на значение атрибута data-src. таким образом картинки начинают загружаться после выполнения данной функции*/
			this.loadImages = function(){
				var curLi = list.find('li').eq(currentPage);
				curLi.find('div > img').attr('src',curLi.find('img').data('src'));
				curLi.prev().find('div > img').attr('src',curLi.prev().find('img').data('src'));
				curLi.next().find('div > img').attr('src',curLi.next().find('img').data('src'));
			};

			if (settings.direction.toLowerCase() === "horizontal") {
				list.css({float:"left"});
				images.each(function(){

					var imgHeight = $(this).height();
					var imgWidth = $(this).width();

					var imgK = imgWidth / imgHeight;
					var fieldK = width / height;
					var imgAttr = {};

					/**
					 * определеям по какой стороне нужно вписать картинку в popup*/

					if (fieldK <= imgK) {
						if (imgWidth < width)
							imagesCss = {'width':imgWidth, 'height':imgHeight,'margin-top':(height-imgHeight)/2};
						else{
							var newHeight = imgHeight - ((imgWidth - width) / imgK);
							imagesCss = {'width':width, 'height':imgHeight - ((imgWidth - width) / imgK),'margin-top':(height-newHeight)/2};
						}

					} else {
						if (imgHeight < height)
							imagesCss = {'height':imgHeight, 'width':imgWidth,'margin-top':(height-imgHeight)/2};

						else
							imagesCss = {'height':height, 'width':imgWidth - ((imgHeight - height) * imgK)};

					}
					$(this).css(imagesCss);

				});
				$.each(pages, function (i) {
					var li = $("<li>")
						.css($.extend(listItemCss, {float:"left"}))
						.html($(this).html());
					list.append(li);
				});


				list.draggable({
					axis:"x",
					start:function (event) {
						settings.beforeStart.apply(list, arguments);

						var data = event.originalEvent.touches ? event.originalEvent.touches[0] : event;
						start = {
							coords:[ data.pageX, data.pageY ]
						};

						settings.afterStart.apply(list, arguments);
					},
					stop:function (event) {
						settings.beforeStop.apply(list, arguments);

						var data = event.originalEvent.touches ? event.originalEvent.touches[0] : event;
						stop = {
							coords:[ data.pageX, data.pageY ]
						};

						start.coords[0] > stop.coords[0] ? moveLeft() : moveRight();

						function moveLeft() {

							if (currentPage === pages.length-1 || dragDelta() < settings.minimumDrag) {
								list.animate({ left:"+=" + dragDelta()}, settings.duration);
								return;
							}
							currentPage++;
							self.slideTo(currentPage);

						}

						function moveRight() {
							if (currentPage === 0 || dragDelta() < settings.minimumDrag) {
								list.animate({ left:"-=" + dragDelta()}, settings.duration);
								return;
							}
							currentPage--;
							self.slideTo(currentPage);


						}

						function dragDelta() {
							return Math.abs(start.coords[0] - stop.coords[0]);
						}

						function adjustment() {
							return width - dragDelta();
						}

					}
				});
			} else if (settings.direction.toLowerCase() === "vertical") {
				$.each(pages, function (i) {
					var li = $("<li>")
						.css(listItemCss)
						.html($(this).html());
					list.append(li);
				});

				list.draggable({
					axis:"y",
					start:function (event) {
						settings.beforeStart.apply(list, arguments);

						var data = event.originalEvent.touches ? event.originalEvent.touches[0] : event;
						start = {
							coords:[ data.pageX, data.pageY ]
						};

						settings.afterStart.apply(list, arguments);
					},
					stop:function (event) {
						settings.beforeStop.apply(list, arguments);

						var data = event.originalEvent.touches ? event.originalEvent.touches[0] : event;
						stop = {
							coords:[ data.pageX, data.pageY ]
						};

						start.coords[1] > stop.coords[1] ? moveUp() : moveDown();

						function moveUp() {
							if (currentPage === pages.length || dragDelta() < settings.minimumDrag) {
								list.animate({ top:"+=" + dragDelta()}, settings.duration);
								return;
							}
							var new_width = -1 * height * currentPage;
							list.animate({ top:new_width}, settings.duration);
							currentPage++;
						}

						function moveDown() {
							if (currentPage === 1 || dragDelta() < settings.minimumDrag) {
								list.animate({ top:"-=" + dragDelta()}, settings.duration);
								return;
							}
							var new_width = -1 * height * (currentPage - 2);
							list.animate({ top:new_width}, settings.duration);
							currentPage--;
						}

						function dragDelta() {
							return Math.abs(start.coords[1] - stop.coords[1]);
						}

						function adjustment() {
							return height - dragDelta();
						}

						settings.afterStop.apply(list, arguments);
					}
				});
			}

			container.append(list);

			originalList.replaceWith(container);
		});
	};
})(jQuery);