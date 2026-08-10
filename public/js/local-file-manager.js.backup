const POSFileManager = (() => {

    const DB_NAME = "POSFileAccess";
    const STORE_NAME = "handles";
    const ROOT_KEY = "rootDir";

    function openDB() {
        return new Promise((resolve, reject) => {

            const request = indexedDB.open(DB_NAME, 1);

            request.onupgradeneeded = () => {
                const db = request.result;

                if (!db.objectStoreNames.contains(STORE_NAME)) {
                    db.createObjectStore(STORE_NAME);
                }
            };

            request.onsuccess = () => {
                resolve(request.result);
            };

            request.onerror = () => {
                reject(request.error);
            };
        });
    }

    async function saveHandle(handle) {

        const db = await openDB();

        return new Promise((resolve, reject) => {

            const transaction =
                db.transaction(STORE_NAME, "readwrite");

            transaction.objectStore(STORE_NAME)
                .put(handle, ROOT_KEY);

            transaction.oncomplete = () => {
                resolve();
            };

            transaction.onerror = () => {
                reject(transaction.error);
            };
        });
    }

    async function loadHandle() {

        const db = await openDB();

        return new Promise((resolve, reject) => {

            const transaction =
                db.transaction(STORE_NAME, "readonly");

            const request =
                transaction.objectStore(STORE_NAME)
                    .get(ROOT_KEY);

            request.onsuccess = () => {
                resolve(request.result || null);
            };

            request.onerror = () => {
                reject(request.error);
            };
        });
    }

    async function getRootHandle() {

        let handle = await loadHandle();

        // First time: ask user to select root folder
        if (!handle) {

            handle = await window.showDirectoryPicker({
                mode: "readwrite"
            });

            await saveHandle(handle);

            return handle;
        }

        // Existing permission
        let permission = await handle.queryPermission({
            mode: "readwrite"
        });

        if (permission === "granted") {
            return handle;
        }

        // Permission needs to be requested again
        permission = await handle.requestPermission({
            mode: "readwrite"
        });

        if (permission === "granted") {
            return handle;
        }

        throw new Error(
            "Root folder permission was not granted."
        );
    }

    async function saveBlob(filename, blob, folders = []) {

        const rootHandle = await getRootHandle();

        let currentHandle = rootHandle;

        for (const folderName of folders) {

            if (!folderName) {
                continue;
            }

            currentHandle =
                await currentHandle.getDirectoryHandle(
                    folderName,
                    {
                        create: true
                    }
                );
        }

        const fileHandle =
            await currentHandle.getFileHandle(
                filename,
                {
                    create: true
                }
            );

        const writable =
            await fileHandle.createWritable();

        await writable.write(blob);

        await writable.close();

        return true;
    }

    async function selectRootFolder() {

        const handle =
            await window.showDirectoryPicker({
                mode: "readwrite"
            });

        await saveHandle(handle);

        return handle;
    }

    return {

        saveBlob,
        selectRootFolder,
        getRootHandle

    };

})();