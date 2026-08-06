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
<script>

let rootHandle = null;

const DB_NAME = "POSFileAccess";
const STORE_NAME = "handles";

/* ---------------------- */
/* IndexedDB Helpers */
/* ---------------------- */

function openDB() {

    return new Promise((resolve, reject) => {

        const request = indexedDB.open(DB_NAME, 1);

        request.onupgradeneeded = function () {

            request.result.createObjectStore(STORE_NAME);

        };

        request.onsuccess = function () {

            resolve(request.result);

        };

        request.onerror = function () {

            reject(request.error);

        };

    });

}

async function saveHandle(handle) {

    const db = await openDB();

    return new Promise((resolve, reject) => {

        const tx = db.transaction(STORE_NAME, "readwrite");

        tx.objectStore(STORE_NAME).put(handle, "rootDir");

        tx.oncomplete = () => resolve();

        tx.onerror = () => reject(tx.error);

    });

}

async function loadHandle() {

    const db = await openDB();

    return new Promise((resolve, reject) => {

        const tx = db.transaction(STORE_NAME, "readonly");

        const request = tx.objectStore(STORE_NAME).get("rootDir");

        request.onsuccess = () => resolve(request.result ?? null);

        request.onerror = () => reject(request.error);

    });

}

/* ---------------------- */
/* Select Folder */
/* ---------------------- */

document.getElementById("btnSelect").addEventListener("click", async () => {

    try {

        rootHandle = await window.showDirectoryPicker();

        await saveHandle(rootHandle);

        alert("Folder saved in IndexedDB successfully.");

    }

    catch (e) {

        console.error(e);

    }

});

/* ---------------------- */
/* Check Saved Folder */
/* ---------------------- */

document.getElementById("btnCheck").addEventListener("click", async () => {

    try {

        const handle = await loadHandle();

        if (!handle) {

            alert("No folder saved.");

            return;

        }

        console.log(handle);

        let permission = await handle.queryPermission({
    mode: "readwrite"
});

if (permission !== "granted") {

    permission = await handle.requestPermission({
        mode: "readwrite"
    });

}

alert("Permission Status : " + permission);

if (permission === "granted") {

    console.log("Permission Granted");

} else {

    console.log("Permission Denied");

}

    }

    catch (e) {

        console.error(e);

    }

});

</script>

</body>
</html>