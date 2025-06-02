<!DOCTYPE html>
<html>
<head>
    <title>Create Post</title>
</head>
<body>
    <h1>Create Post</h1>
    <form action="{{ route('posts.store') }}" method="POST">
        @csrf
        <div>
            <label for="title">Title:</label>
            <input type="text" id="title" name="title">
        </div>
        <div>
            <label for="content">Content:</label>
            <textarea id="content" name="content"></textarea>
        </div>
        <button type="submit">Create</button>
    </form>
</body>
</html>
