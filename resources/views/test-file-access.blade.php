<script>
let rootHandle = null;

// Select Folder
document.getElementById('btnSelect').addEventListener('click', async () => {
    try {
        rootHandle = await window.showDirectoryPicker();

        console.log(rootHandle);

        alert("Folder Selected Successfully");

        // Test file save
        await saveTestFile();

    } catch (e) {
        console.error(e);
        alert("Operation Cancelled");
    }
});

// Save Test File
async function saveTestFile() {

    // Create/Get test.txt
    const fileHandle = await rootHandle.getFileHandle("test.txt", {
        create: true
    });

    // Open writable stream
    const writable = await fileHandle.createWritable();

    // Write data
    await writable.write(`Hello Suleman

This file was created from your Laravel POS.

Date: ${new Date().toLocaleString()}

If you are reading this,
File System Access API is working successfully.
`);

    // Save file
    await writable.close();

    alert("test.txt created successfully!");
}
</script>