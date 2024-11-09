<!-- resources/views/profile.blade.php -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Save Profile Data</title>
</head>
<body>
    <h1>Save Profile Data</h1>
    <form action="{{ route('saveProfileData') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <!-- Profile Information -->
        <label>Title:</label>
        <input type="text" name="title"><br>

        <label>Description:</label>
        <textarea name="description"></textarea><br>

        <!-- Phones (Array) -->
        <label>Phones:</label>
        <input type="text" name="phones[]"><br>
        <input type="text" name="phones[]"><br>

        <!-- Logo Upload -->
        <label>Logo:</label>
        <input type="file" name="logo"><br>

        <!-- Cover Upload -->
        <label>Cover:</label>
        <input type="file" name="cover"><br>

        <!-- Background Color -->
        <label>Background Color:</label>
        <input type="text" name="color"><br>

        <!-- Font -->
        <label>Font:</label>
        <input type="text" name="font"><br>

        <!-- Package ID -->
        <label>Package ID:</label>
        <input type="text" name="package_id"><br>

        <!-- Links (Array of URL and Type) -->
        <label>Links:</label>
        <input type="text" name="links[0][url]" placeholder="URL">
        <input type="text" name="links[0][type]" placeholder="Type"><br>

        <input type="text" name="links[1][url]" placeholder="URL">
        <input type="text" name="links[1][type]" placeholder="Type"><br>

        <!-- Images (Multiple File Upload) -->
        <label>Images:</label>
        <input type="file" name="images[]" multiple><br>

        <!-- PDFs (Multiple File Upload) -->
        <label>PDFs:</label>
        <input type="file" name="pdfs[]" multiple><br>

        <!-- Event Information -->
        <label>Event Date:</label>
        <input type="date" name="event_date"><br>

        <label>Event Time:</label>
        <input type="time" name="event_time"><br>

        <label>Location:</label>
        <input type="text" name="location"><br>

        <!-- Branches (Array of Branches with Name, Location, and Phones) -->
        <label>Branches:</label>
        <input type="text" name="branches[0][name]" placeholder="Branch Name">
        <input type="text" name="branches[0][location]" placeholder="Location">
        <input type="text" name="branches[0][phones][]" placeholder="Phone 1">
        <input type="text" name="branches[0][phones][]" placeholder="Phone 2"><br>

        <input type="text" name="branches[1][name]" placeholder="Branch Name">
        <input type="text" name="branches[1][location]" placeholder="Location">
        <input type="text" name="branches[1][phones][]" placeholder="Phone 1">
        <input type="text" name="branches[1][phones][]" placeholder="Phone 2"><br>

        <!-- Submit Button -->
        <button type="submit">Save Profile Data</button>
    </form>
</body>
</html>
