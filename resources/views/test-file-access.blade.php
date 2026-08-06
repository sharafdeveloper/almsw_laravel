<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>File System API Test</title>
</head>
<body>

<h2>File System API Test</h2>

<button id="btnSelect">Select Root Folder</button>

<button id="btnCheck">Check Saved Folder</button>

<button id="btnSave">Save Test File</button>
<script>

let rootHandle = null;

const DB_NAME = "POSFileAccess";
const STORE_NAME = "handles";

/* ===========================
   OPEN DATABASE
=========================== */

function openDB() {

    return new Promise((resolve, reject) => {

        const request = indexedDB.open(DB_NAME, 1);

        request.onupgradeneeded = () => {

            request.result.createObjectStore(STORE_NAME);

        };

        request.onsuccess = () => resolve(request.result);

        request.onerror = () => reject(request.error);

    });

}

/* ===========================
   SAVE HANDLE
=========================== */

async function saveHandle(handle) {

    const db = await openDB();

    return new Promise((resolve, reject) => {

        const tx = db.transaction(STORE_NAME, "readwrite");

        tx.objectStore(STORE_NAME).put(handle, "rootDir");

        tx.oncomplete = () => resolve();

        tx.onerror = () => reject(tx.error);

    });

}

/* ===========================
   LOAD HANDLE
=========================== */

async function loadHandle() {

    const db = await openDB();

    return new Promise((resolve, reject) => {

        const tx = db.transaction(STORE_NAME, "readonly");

        const request = tx.objectStore(STORE_NAME).get("rootDir");

        request.onsuccess = () => resolve(request.result ?? null);

        request.onerror = () => reject(request.error);

    });

}

/* ===========================
   SELECT ROOT FOLDER
=========================== */

document.getElementById("btnSelect").addEventListener("click", async () => {

    try {

        rootHandle = await window.showDirectoryPicker();

        await saveHandle(rootHandle);

        alert("Root Folder Saved Successfully.");

    }

    catch (e) {

        console.error(e);

    }

});

/* ===========================
   SAVE TEST FILE
=========================== */

document.getElementById("btnSave").addEventListener("click", async () => {

    try {

        let handle = await loadHandle();

        if (!handle) {

            alert("Please select root folder first.");

            return;

        }

        let permission = await handle.queryPermission({
            mode: "readwrite"
        });

        if (permission !== "granted") {

            permission = await handle.requestPermission({
                mode: "readwrite"
            });

        }

        if (permission !== "granted") {

            alert("Permission denied.");

            return;

        }

        const fileHandle = await handle.getFileHandle("test2.txt", {
            create: true
        });

        const writable = await fileHandle.createWritable();

        await writable.write(`Congratulations!

IndexedDB loaded the folder successfully.

Date:
${new Date().toLocaleString()}

This file was created WITHOUT selecting the folder again.
`);

        await writable.close();

        alert("test2.txt created successfully.");

    }

    catch (e) {

        console.error(e);

        alert(e.message);

    }

});

</script>

</body>
</html>