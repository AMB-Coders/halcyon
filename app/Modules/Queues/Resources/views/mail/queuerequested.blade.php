@component('mail::message')
Hello {{ $user->name }},

The following people have been requested access for the following ITaP Research Computing resources and queues that you manage.

@foreach ($requests as $user_id => $data)
---

{{ $data['user']->name }} ({{ $data['user']->email }}):

@foreach ($data['queueusers'] as $userqueue)
@if ($userqueue->queue)
* {{ $userqueue->queue->resource->name }}: '{{ $userqueue->queue->name }}' queue
@if ($userqueue->request && $userqueue->request->comment)
    * Comment: {{ $userqueue->request->comment }}
@endif
@endif
@endforeach

@endforeach

---

You may approve or deny these requests on the [Queue Management website](https://www.rcac.purdue.edu/account/user). You will be presented with a menu to approve or deny requests upon logging in. Once the request is approved the requestor will be notified via email. No changes to the person's access will be made if a request is denied.
@endcomponent