@extends('docs.layout')

@section('docs-content')
    <div class="px-lg-5 px-4">
        <h2 class="mb-4 font-weight-medium">REST API</h2> <!-- main content -->
        <div class="content">
            <p>
                <strong>Zaraz</strong> is a service designed to send broadcast messages asynchronously for Telegram bots.
                It ensures efficient and reliable message distribution, supporting advanced customization and handling
                large-scale message delivery seamlessly.
            </p>
            <hr>
            <div class="endpoint" id="create_task">
                <h4>Creating task</h4>
                <p>POST: <span class="text-info">{{ route('tasks.store') }}</span></p>
                <h6 id="tables">Request body</h6>
                <table>
                    <thead>
                    <tr>
                        <th>Field</th>
                        <th>Type</th>
                        <th>Required</th>
                        <th>Description</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>token</td>
                        <td>String</td>
                        <td>Yes</td>
                        <td>Bot token which taken from Botfather</td>
                    </tr>
                    <tr>
                        <td>method</td>
                        <td>String</td>
                        <td>Yes</td>
                        <td>
                            Available methods:
                            @foreach(\App\Enums\SendMethod::cases() as $method)
                                <a class="text-decoration-none" href="{{ $method->documentationLink() }}">
                                    {{ $method->value }}</a>,
                            @endforeach
                        </td>
                    </tr>
                    <tr>
                        <td>chats</td>
                        <td>Array of integers</td>
                        <td>Yes</td>
                        <td><code>chat_id</code>s for sending message. They must be unique.</td>
                    </tr>
                    <tr>
                        <td>params</td>
                        <td>Array</td>
                        <td>Yes</td>
                        <td>See available parameters for chosen method.</td>
                    </tr>
                    <tr>
                        <td>test_chat_id</td>
                        <td>Integer</td>
                        <td>Yes</td>
                        <td>chat_id to send test message.</td>
                    </tr>
                    <tr>
                        <td>webhook</td>
                        <td>String</td>
                        <td>Optional</td>
                        <td>Webhook URL for sending results after task finished.</td>
                    </tr>
                    </tbody>
                </table>
                <div class="border border-default collapse-wrapper">
                    <a class="d-flex py-2 collapse-head" data-toggle="collapse" href="#create_task_request" role="button">
                        Request example <i class="ti-plus ml-auto"></i>
                    </a>
                    <div class="collapse" id="create_task_request">
                    <pre><code>{
    "token": "123456789:hHaWRTyFSp9k39c70gj7hc1sypmMm2smrqU",
    "method": "sendMessage",
    "chats": [
        123456789,
        345678912,
        789123456
    ],
    "params": {
        "text": "Hello &lt;b&gt;world&lt;/b&gt;!",
        "parse_mode": "HTML"
    },
    "test_chat_id": 123456789,
    "webhook": "https://example.com/broadcast/webhook"
}</code></pre>
                    </div>
                </div>
                <div class="border border-default collapse-wrapper">
                    <a class="d-flex py-2 collapse-head" data-toggle="collapse" href="#create_task_response" role="button">
                        Response example <i class="ti-plus ml-auto"></i>
                    </a>
                    <div class="collapse" id="create_task_response">
                    <pre><code>{
    "data": {
        "id": "01943773-97f4-710c-b6d0-bcc092d25795",
        "bot_id": 123456789,
        "username": "tetris_bot",
        "status": "pending",
        "webhook": "https://example.com/broadcast/webhook",
        "started_at": null,
        "finished_at": null
    },
    "message": "Task queued"
}</code></pre>
                    </div>
                </div>
            </div>
            <hr>
            <div class="endpoint" id="show_task">
                <h4>Show task</h4>
                <p>GET: <span class="text-info">{{ route('tasks.show', '') }}/</span><span class="text-danger">{task_uuid}</span></p>
                <div class="border border-default collapse-wrapper">
                    <a class="d-flex py-2 collapse-head" data-toggle="collapse" href="#show_task_response" role="button">
                        Response example <i class="ti-plus ml-auto"></i>
                    </a>
                    <div class="collapse" id="show_task_response">
                    <pre><code>{
    "data": {
        "id": "01943773-97f4-710c-b6d0-bcc092d25795",
        "bot_id": 123456789,
        "username": "tetris_bot",
        "status": "in_progress",
        "webhook": "https://example.com/broadcast/webhook",
        "started_at": "2025-01-18T11:16:44.000000Z",
        "finished_at": null,
        "stats": {
            "forbidden": 4427,
            "pending": 12267,
            "to_many_requests": 31
        }
    }
}</code></pre>
                    </div>
                </div>
            </div>
            hr
            <div class="endpoint" id="cancel_task">
                <h4>Cancel task</h4>
                <p>DELETE: <span class="text-info">{{ route('tasks.destroy', '') }}/</span><span class="text-danger">{task_uuid}</span></p>
                <div class="border border-default collapse-wrapper">
                    <a class="d-flex py-2 collapse-head" data-toggle="collapse" href="#delete_task_response" role="button">
                        Response example <i class="ti-plus ml-auto"></i>
                    </a>
                    <div class="collapse" id="delete_task_response">
                    <pre><code>{
    "data": {
        "id": "01943773-97f4-710c-b6d0-bcc092d25795",
        "bot_id": 123456789,
        "username": "tetris_bot",
        "status": "cancelled",
        "webhook": "https://example.com/broadcast/webhook",
        "started_at": "2025-01-18T11:16:44.000000Z",
        "finished_at": null,
        "stats": {
            "forbidden": 4427,
            "pending": 12267,
            "to_many_requests": 31
        }
    },
    "message": "Task cancelled successfully."
}</code></pre>
                    </div>
                </div>
            </div>
        </div>

        <!-- navigation -->
        <nav class="pagination">
            <a class="nav nav-prev" href="{{ route('home') }}"><i class="ti-arrow-left mr-2"></i>
                <span class="d-none d-md-block">Back to home</span></a>
            <a class="nav nav-next" href="https://examplesite.com/basic-startup/requirments/"> <span
                    class="d-none d-md-block">Requirments</span><i class="ti-arrow-right ml-2"></i></a>
        </nav>
    </div>
@endsection
