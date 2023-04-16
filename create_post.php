<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Post</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Create Post</h1>
        <form action="submit_post.php" method="post" enctype="multipart/form-data">
            <div class="input-field">
                <label for="content">Content</label>
                <textarea name="content" id="content" rows="5" required></textarea>
            </div>
            <div class="input-field">
                <label for="image">Upload an Image (optional)</label>
                <input type="file" name="image" id="image">
            </div>
            <button type="submit">Submit Post</button>
        </form>
    </div>
</body>
</html>
