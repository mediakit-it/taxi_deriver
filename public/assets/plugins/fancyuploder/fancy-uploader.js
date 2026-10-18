(function($) {
	//fancyfileuplod
	$('#demo').FancyFileUpload({
		maxfilesize : 1000000,
		url: '/admin/productCreateImages',
		params: {
            _token: $('#csrf_token').val()
        },
        onComplete: function(response) {
            var uploadedFiles = JSON.parse(response);
            // Теперь uploadedFiles содержит список загруженных файлов с их ссылками
            console.log(uploadedFiles);
            // Вы можете использовать этот список для дальнейшей обработки
        }
	});
})(jQuery);