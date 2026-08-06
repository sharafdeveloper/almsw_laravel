<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>File System API Test</title>
</head>
<body>

<h2>File System API Test</h2>

<button id="btnSelect">Select Root Folder</button>

<script>

let rootHandle = null;

// Select Folder
document.getElementById('btnSelect').addEventListener('click', async () => {

    try {

        rootHandle = await window.showDirectoryPicker();

        console.log(rootHandle);

        alert("Folder Selected Successfully");

        await saveTestFile();

    } catch (e) {

        console.error(e);

        alert(e.message);

    }

});


async function saveTestFile() {

    const fileHandle = await rootHandle.getFileHandle("test.txt", {
        create: true
    });

    const writable = await fileHandle.createWritable();

    await writable.write(`Hello Suleman

This file was created from your Laravel POS.

Date: ${new Date().toLocaleString()}

File System Access API Working Successfully.
`);

    await writable.close();

    alert("test.txt created successfully!");

}

</script>

</body>
</html>