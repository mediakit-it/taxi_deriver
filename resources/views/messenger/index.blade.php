@extends('admin.master')

@section('style')
    <style>
        .card-body {
            height: 65vh;
        }
        .message_list {
            background: #fbfbfb;
            height: 100%;

            border-radius: 15px;
            padding: 15px;

            overflow-y: auto;
        }

        .message_area_block {
            background: #fbfbfb;
            height: 100%;

            border-radius: 15px;
            padding: 15px;
        }

        .message_item_user {
            cursor: pointer;

            width: 100%;
            height: 70px;
            margin-bottom: 5px;

            border: 1px solid #ddd;
            border-radius: 12px;

            display: flex;
            align-items: center;
            gap: 10px;

            position: relative;
        }

        .message_item_user:hover {
            background: #fff;
        }

        .message_item_user .user_image {
            height: 100%;
            width: 70px;

            display: flex;
            align-items: center;
            justify-content: center;
        }

        .message_item_user .small_message_info {
            display: flex;
            flex-direction: column;

            width: calc(100% - 5%);
        }

        .small_message_info .user_name {
            font-size: 16px;
            font-weight: 600;

            margin-bottom: 5px;

            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical; 

            width: calc(100% - 35px);
        }

        .small_message_info .small_message_area {
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical; 

            width: calc(100% - 25px);
        }

        .message_item_user .unread_message {
            width: 10px;
            height: 10px;

            /* background: #538999; */
            background: red;
            border-radius: 50%;

            position: absolute;
            bottom: 14px;
            right: 14px;
        }

        .message_item_user .last_message_time {
            position: absolute;
            top: 12px;
            right: 14px;
        }

        .message_area_block .message_area {
            width: 100%;
            height: 100%;

            position: relative;
        }

        .empty_message_area {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
        }

        .empty_message_area p {
            font-size: 18px;
            text-align: center;
        }

        .message_area .message_area_header {
            position: absolute;
            top: 0;
            width: 100%;

            display: flex;
            align-items: center;
            gap: 10px;

            padding: 0 10px 10px;

            background: #fbfbfb;
            border-bottom: 1px solid #ddd;
        }

        .message_area_header .user_name a{
            font-size: 16px;
            font-weight: 600;

            color: #495057;
        }

        .message_area_header .user_name a:hover {
            color: #3b78b5;
        }

        .message_area .message_area_footer { 
            position: absolute;
            bottom: 0;
            width: 100%;
            height: 70px;

            display: flex;
            align-items: center;
            gap: 10px;

            background: #fbfbfb;
            border-top: 1px solid #ddd;
        }

        .message_area_main {
            height: 100%;
            width: 100%;
            overflow-y: auto;

            padding-top: 62px;
            padding-bottom: 71px;

            display: flex;
            flex-direction: column;
        }

        .message_area_main .user_message {
            max-width: 40%;
            min-width: 90px;
            padding: 10px;

            background: #e6e6e6;
            border-radius: 12px;
            float: left;
        }

        .message_area_main .admin_message {
            max-width: 40%;
            min-width: 90px;
            padding: 10px;

            color: #fff;
            background: #8fbd56;
            border-radius: 12px;

            float: right;

        }

        #new_message_text {
            height: 50px;
        }
    </style>
@endsection

@section('content')
<div class="side-app">
	<!-- CONTAINER -->
	<div class="main-container container-fluid">

		<!-- PAGE-HEADER -->
		<div class="page-header">
			<div>
				<h1 class="page-title">Сообщения</h1>
			</div>
			<div class="ms-auto pageheader-btn">
				<ol class="breadcrumb">
					<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Главная</a></li>
					<li class="breadcrumb-item active" aria-current="page">Сообщения</li>
				</ol>
			</div>
		</div>
		<!-- PAGE-HEADER END -->

		<!-- Row -->
		<div class="row row-sm">
            <div class="col-lg-12">
				<div class="card">
                    <div class="card-header border-bottom">
						<h3 class="card-title">Список сообщений</h3>
					</div>
					<div class="card-body row overflow-hidden;">
                        <div class="col-4 mh-100">
                            <div class="message_list">
                                @foreach($users as $user)
                                    <div class="message_item_user" data-id="{{$user->id}}">
                                        <div class="user_image">
                                            <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="50" height="50" x="0" y="0" viewBox="0 0 53 53" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><g><path d="m18.613 41.552-7.907 4.313a7.106 7.106 0 0 0-1.269.903A26.377 26.377 0 0 0 26.5 53c6.454 0 12.367-2.31 16.964-6.144a7.015 7.015 0 0 0-1.394-.934l-8.467-4.233a3.229 3.229 0 0 1-1.785-2.888v-3.322c.238-.271.51-.619.801-1.03a19.482 19.482 0 0 0 2.632-5.304c1.086-.335 1.886-1.338 1.886-2.53v-3.546c0-.78-.347-1.477-.886-1.965v-5.126s1.053-7.977-9.75-7.977-9.75 7.977-9.75 7.977v5.126a2.644 2.644 0 0 0-.886 1.965v3.546c0 .934.491 1.756 1.226 2.231.886 3.857 3.206 6.633 3.206 6.633v3.24a3.232 3.232 0 0 1-1.684 2.833z" style="" fill="#e7eced" data-original="#e7eced" class=""></path><path d="M26.953.004C12.32-.246.254 11.414.004 26.047-.138 34.344 3.56 41.801 9.448 46.76a7.041 7.041 0 0 1 1.257-.894l7.907-4.313a3.23 3.23 0 0 0 1.683-2.835v-3.24s-2.321-2.776-3.206-6.633a2.66 2.66 0 0 1-1.226-2.231v-3.546c0-.78.347-1.477.886-1.965v-5.126S15.696 8 26.499 8s9.75 7.977 9.75 7.977v5.126c.54.488.886 1.185.886 1.965v3.546c0 1.192-.8 2.195-1.886 2.53a19.482 19.482 0 0 1-2.632 5.304c-.291.411-.563.759-.801 1.03V38.8c0 1.223.691 2.342 1.785 2.888l8.467 4.233a7.05 7.05 0 0 1 1.39.932c5.71-4.762 9.399-11.882 9.536-19.9C53.246 12.32 41.587.254 26.953.004z" style="" fill="#556080" data-original="#556080" class=""></path></g></svg>
                                        </div>
                                        <div class="small_message_info">
                                            <div class="user_name">{{$user->first_name}} {{$user->name}}</div>
                                            <!-- <div class="small_message_area">Привет, ребята, у меня тут вопрос. Если я тестирую тест, то тест — это тестируемое или тестируемый? А если тест тестирует тест? Короче, я запутался в своем тесте.</div> -->
                                            <div class="small_message_area">{{$user->email}}</div>
                                        </div>

                                        <div class="unread_message {{$user->unreadMessagesCount() == 0 ? 'd-none' : ''}}"></div>
                                        <!-- <div class="last_message_time">21:37</div> -->
                                    </div>
                                @endforeach
                            
                            </div>
                        </div>
                        <div class="col-8 mh-100">
                            <div class="message_area_block">
                                <div class="empty_message_area">
                                    <p class="empty_message_text">Пожалуйста, выберите человека из списка, <br> с которым вы будете общаться</p>
                                </div>

                                <div class="message_area d-none" id="messageElement">
                                    
                                </div>
                            </div>
                        </div>
				    </div>
				</div>
			</div>
		</div>
		<!-- End Row -->

		<div class="modal fade"  id="modaldemo1">
			
		</div>
	</div>
</div>
@endsection


@section('scripts')

	<script>
        function getCurrentTime() {
            var now = new Date();

            var hours = now.getHours();
            var minutes = now.getMinutes();

            if (minutes < 10) {
                minutes = '0' + minutes;
            }

            var time = hours + ':' + minutes;

            return time;
        }

        // пролистывание диалога вниз
        function scrollToBottom() {
            var messageArea = document.getElementById('messageAreaMain');
            messageArea.scrollTop = messageArea.scrollHeight;
        }

        // добавление нового сообщения после отправки
        function addMessage(message,userId) {

            $.ajax({
                type: 'POST',
                url: "/admin/addNewMessageUser",
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                data: {'userId': userId, 'message':message},
                success: function (data) {
                    $('#messageAreaMain').append(data.htmlMessage);
                    scrollToBottom();
                },
                error: function (error) {
                    console.log(error);
                }
            });
        }
        

		$(document).ready(function() {

			// переход из тоста о новом сообщении: /admin/messenger?user={id}
            var openUserId = new URLSearchParams(window.location.search).get('user');
            if (openUserId) {
                $('.message_item_user[data-id="' + openUserId + '"]').first().trigger('click');
            }

			$(document).on('click', '.message_item_user', function () {
                var userId = $(this).data('id');
                var element = $(this);

                $.ajax({
                    type: 'POST',
                    url: "/admin/getMessagesUser",
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}"
                    },
                    data: {'userId': userId},
                    success: function (data) {
                        $('.empty_message_area').addClass('d-none');
                        $('.message_area').html(data.htmlMessage);
                        $('.message_area').removeClass('d-none');
                        element.find('.unread_message').addClass('d-none');
                        // Открытый диалог для admin-alerts.js: пока он
                        // открыт, тост и звук о новых сообщениях этого
                        // пользователя не нужны - они и так в ленте.
                        window.__messengerOpenUserId = userId;

                        scrollToBottom();
                    },
                    error: function (error) {
                        console.log(error);
                    }
                });
                
            });

            // обработка нажатия на кнопку отправки
            $(document).on('click', '#sent_new_message_btn', function (e) {
                e.preventDefault();
                let message = $('#new_message_text').val();
                var userId = $(this).data('id');
                if (message != '') {
                    addMessage(message,userId);
                    $('#new_message_text').val('');
                }
            });

            // обработка нажатия на Enter
            $(document).on('keypress', '#new_message_text', function(event) {
                if (event.which === 13) { 
                    $('#sent_new_message_btn').click();
                }
            });
		});		
	</script>

@endsection