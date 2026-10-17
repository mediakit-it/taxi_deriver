<div class="message_area_header">
    <div class="user_image">
        <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="50" height="50" x="0" y="0" viewBox="0 0 53 53" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><g><path d="m18.613 41.552-7.907 4.313a7.106 7.106 0 0 0-1.269.903A26.377 26.377 0 0 0 26.5 53c6.454 0 12.367-2.31 16.964-6.144a7.015 7.015 0 0 0-1.394-.934l-8.467-4.233a3.229 3.229 0 0 1-1.785-2.888v-3.322c.238-.271.51-.619.801-1.03a19.482 19.482 0 0 0 2.632-5.304c1.086-.335 1.886-1.338 1.886-2.53v-3.546c0-.78-.347-1.477-.886-1.965v-5.126s1.053-7.977-9.75-7.977-9.75 7.977-9.75 7.977v5.126a2.644 2.644 0 0 0-.886 1.965v3.546c0 .934.491 1.756 1.226 2.231.886 3.857 3.206 6.633 3.206 6.633v3.24a3.232 3.232 0 0 1-1.684 2.833z" style="" fill="#e7eced" data-original="#e7eced" class=""></path><path d="M26.953.004C12.32-.246.254 11.414.004 26.047-.138 34.344 3.56 41.801 9.448 46.76a7.041 7.041 0 0 1 1.257-.894l7.907-4.313a3.23 3.23 0 0 0 1.683-2.835v-3.24s-2.321-2.776-3.206-6.633a2.66 2.66 0 0 1-1.226-2.231v-3.546c0-.78.347-1.477.886-1.965v-5.126S15.696 8 26.499 8s9.75 7.977 9.75 7.977v5.126c.54.488.886 1.185.886 1.965v3.546c0 1.192-.8 2.195-1.886 2.53a19.482 19.482 0 0 1-2.632 5.304c-.291.411-.563.759-.801 1.03V38.8c0 1.223.691 2.342 1.785 2.888l8.467 4.233a7.05 7.05 0 0 1 1.39.932c5.71-4.762 9.399-11.882 9.536-19.9C53.246 12.32 41.587.254 26.953.004z" style="" fill="#556080" data-original="#556080" class=""></path></g></svg>
    </div>
    <div class="user_name">
        <a href="{{route('user.edit',['user'=>$userMessage->id])}}" target="_blank">{{$userMessage->first_name}} {{$userMessage->name}}</a>
    </div>
</div>

<div class="message_area_main" id="messageAreaMain">
    @foreach($messagesUser as $message)
        @if($message->type=="user")
            <div class="w-100 mb-2">
                <div class="user_message">
                    {{$message->message}}
                    <div style="text-align: right; font-size: 10px;">{{$message->created_at->format('H:i')}}</div>
                </div>
            </div>
        @else
            <div class="w-100 mb-2">
                <div class="admin_message">
                    {{$message->message}}
                    <div style="text-align: right; font-size: 10px;">{{$message->created_at->format('H:i')}}</div>
                </div>
            </div>
        @endif
    @endforeach
</div>

<div class="message_area_footer">
    <div class="form-group mb-0 w-100">
        <input class="form-control" type="text" id="new_message_text" placeholder="Введите ваше сообщение..">
    </div>

    <div class="send_button_area">
        <button class="btn btn-primary" id="sent_new_message_btn" data-id="{{$userMessage->id}}">
            <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="25" height="25" x="0" y="0" viewBox="0 0 24 24" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><g><path fill="#ffffff" d="M22.101 10.562 2.753 1.123A1.219 1.219 0 0 0 1 2.22v.035a2 2 0 0 0 .06.485l1.856 7.424a.5.5 0 0 0 .43.375l8.157.907a.559.559 0 0 1 0 1.11l-8.157.907a.5.5 0 0 0-.43.375L1.06 21.261a2 2 0 0 0-.06.485v.035a1.219 1.219 0 0 0 1.753 1.096L22.1 13.438a1.6 1.6 0 0 0 0-2.876z" opacity="1" data-original="#000000" class=""></path></g></svg>
        </button>
    </div>
</div>