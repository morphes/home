var competition = function(){
	function initInviteForm(){
		$('#inviteFormBlock')
			.on('click', '#btnToggleForm', function(e){
				$(e.delegateTarget).children().toggleClass('-hidden').end().toggleClass('highlighted');
			})
			.on('click', '#submitInviteForm', function(e){
				emailInvite = $(".emailInvite").val();
				$.ajax({
					type: "POST",
					url: "/content/static/AjaxSendInvite",
					async: false,
					data: {'item':{'email':emailInvite}},
					dataType:"json",
					success:function(response){
						if(response.success==true)
						{
							$(e.delegateTarget).children(':not(.-icon-cross)').addClass('-hidden').end().toggleClass('highlighted');
							$('<div class="-col-7 -inset-top" id="formSuccess">').appendTo('#inviteFormBlock');
							$('<span>Приглашение успешно отправлено!</span> <a class="-red" id="resetInviteForm" href="#">Отправить еще</a>').appendTo('#formSuccess');

						}
						else{
							$(".emailInvite").addClass('error');
						}
					}
				});

				return false;

			})
			.on('click', '#resetInviteForm', function(e){
				$('#formSuccess').remove();
				$(e.delegateTarget).children('.-col-7').toggleClass('-hidden').end().toggleClass('highlighted');
				return false;
			})
			.on('click', '#closeBtn', function(e){
				$('#formSuccess').remove();
				$(e.delegateTarget).removeClass('highlighted').children().addClass('-hidden').filter('.-col-5, .-col-3').removeClass('-hidden');
			});
	}
	return {
		initInviteForm:initInviteForm
	}
}();