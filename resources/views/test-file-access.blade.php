<!DOCTYPE html>
<html>
<head>
    <title>File Access Test</title>
</head>
<body>

<h2>File System API Test</h2>

<button id="btnSelect">Select Root Folder</button>

<script>
document.getElementById('btnSelect').addEventListener('click', async () => {
    try {
        const handle = await window.showDirectoryPicker();

        console.log(handle);

        alert("Folder Selected Successfully");
    } catch (e) {
        console.error(e);
        alert("User cancelled.");
    }
});
</script>

</body>
</html>