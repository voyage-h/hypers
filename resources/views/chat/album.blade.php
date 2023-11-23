<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <title>{{$me->name ?? env('APP_NAME')}}</title>
    <link rel="stylesheet" href="/chat/album.css">
    <link rel="stylesheet" href="/photoswipe/photoswipe.css">
</head>
<body>
<div class="chat-home"><a href="/"><img src="/chat/home.png"></a></div>
<div class="albums-container">
<div class="chat-title">
    <a href="#">{{$me->name}}{{$me->note ? '(' . $me->note . ')' : ''}}</a>
    <div class="title-basic">{{$me->age}} {{$me->height}} {{$me->weight}} {{$me->role}}</div>
</div>
<div class="albums">
    @foreach($me->albums as $album)
        <div class="album">
            <a href="{{$album->contents}}" class="contents-img-a" data-pswp-src="{{$album->contents}}">
                <img src="{{$album->contents}}">
            </a>
        </div>
    @endforeach
</body>
</div>
@include('components.photoswipe', ['gallery' => '.albums', 'children' => '.contents-img-a'])
</html>
