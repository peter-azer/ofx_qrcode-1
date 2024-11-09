<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Smart QR Code</title>
    <style>
        /* Add your CSS styling here */
        .container { max-width: 500px; margin: auto; padding: 20px; background-color: #f4f4f4; border-radius: 8px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; }
        .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 8px; }
        .form-group input[type="file"] { padding: 3px; }
        .submit-btn { width: 100%; padding: 10px; background-color: #073763; color: #fff; border: none; cursor: pointer; }
        #qr-code-image { display: none; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Generate Smart QR Code</h2>
        <form id="qrCodeForm" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" id="title" name="title">
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description"></textarea>
            </div>
            <div class="form-group">
                <label for="phones">Phones (comma separated)</label>
                <input type="text" id="phones" name="phones[]" placeholder="123456789,987654321">
            </div>
            <div class="form-group">
                <label for="logo">Logo</label>
                <input type="file" id="logo" name="logo">
            </div>
            <div class="form-group">
                <label for="cover">Cover Image</label>
                <input type="file" id="cover" name="cover">
            </div>
            <div class="form-group">
                <label for="color">Color</label>
                <input type="text" id="color" name="color" placeholder="#000000">
            </div>
            <div class="form-group">
                <label for="font">Font</label>
                <input type="text" id="font" name="font">
            </div>
            <div class="form-group">
                <label for="package_id">Package ID</label>
                <select id="package_id" name="package_id">
                    <option value="1">Package 1</option>
                    <option value="2">Package 2</option>
                </select>
            </div>
            <div class="form-group">
                <label for="links">Links (URL and type)</label>
                <input type="text" name="links[0][url]" placeholder="URL">
                <input type="text" name="links[0][type]" placeholder="Type">
                <input type="text" name="links[1][url]" placeholder="URL">
                <input type="text" name="links[1][type]" placeholder="Type">
            </div>
            <div class="form-group">
                <label for="images">Images</label>
                <input type="file" id="images" name="images[]" multiple>
            </div>
            <div class="form-group">
                <label for="mp3">MP3 Files</label>
                <input type="file" id="mp3" name="mp3[]" multiple>
            </div>
            <div class="form-group">
                <label for="pdfs">PDF Files</label>
                <input type="file" id="pdfs" name="pdfs[]" multiple>
            </div>
            <div class="form-group">
                <label for="event_date">Event Date</label>
                <input type="date" id="event_date" name="event_date">
            </div>
            <div class="form-group">
                <label for="event_time">Event Time</label>
                <input type="time" id="event_time" name="event_time">
            </div>
            <div class="form-group">
                <label for="location">Location</label>
                <input type="text" id="location" name="location">
            </div>
            <div class="form-group">
                <label for="branches">Branches</label>
                <input type="text" name="branches[0][name]" placeholder="Branch Name">
                <input type="text" name="branches[0][location]" placeholder="Branch Location">
                <input type="text" name="branches[0][phones][]" placeholder="Phone 1">
                <input type="text" name="branches[0][phones][]" placeholder="Phone 2">
            </div>
            <button type="submit" class="submit-btn">Generate QR Code</button>
        </form>

        <!-- QR Code Image -->
        <img id="qr-code-image" src="" alt="Generated QR Code">

        <!-- Success Message -->
        <p id="message"></p>
    </div>

    <script>
       document.getElementById('qrCodeForm').addEventListener('submit', function(event) {
    event.preventDefault();

    let formData = new FormData(this);

    fetch('{{ url('/generate-qr') }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => {
        // Check if the response is ok (status code 200-299)
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.qr_code_path) {
            // Show QR code image
            document.getElementById('qr-code-image').src = data.qr_code_path;
            document.getElementById('qr-code-image').style.display = 'block';
            document.getElementById('message').innerText = data.message;
        } else if (data.errors) {
            console.error("Validation errors:", data.errors);
            document.getElementById('message').innerText = "Please fix the validation errors.";
        } else {
            console.error('Failed to generate QR code:', data);
            alert('Failed to generate QR code.');
        }
    })
    .catch(error => {
        // Log any error that occurred during the fetch
        console.error('Error:', error);
        document.getElementById('message').innerText = "An error occurred. Please check the console for details.";
    });
});

    </script>
</body>
</html>
