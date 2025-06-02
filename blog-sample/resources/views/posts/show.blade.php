<!DOCTYPE html>
<html>
<head>
    <title>{{ $post->title }}</title>
</head>
<body>
    <h1>{{ $post->title }}</h1>
    <p>{{ $post->content }}</p>
    <a href="{{ route('posts.index') }}">Back to Posts</a>
    <a href="{{ route('posts.edit', $post) }}">Edit</a>
    <form action="{{ route('posts.destroy', $post) }}" method="POST" style="display:inline;">
        @csrf
        @method('DELETE')
        <button type="submit">Delete</button>
    </form>
</body>
</html>
