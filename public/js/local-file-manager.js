const POSFileManager = (() => {

    const DB_NAME = "POSFileAccess";
    const STORE_NAME = "handles";

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

    async function loadRootHandle() {

        const db = await openDB();

        return new Promise((resolve, reject) => {

            const tx = db.transaction(STORE_NAME, "readonly");

            const request =
                tx.objectStore(STORE_NAME).get("rootDir");

            request.onsuccess = () => {
                resolve(request.result || null);
            };

            request.onerror = () => {
                reject(request.error);
            };

        });
    }

    async function ensurePermission(handle) {

        let permission = await handle.queryPermission({
            mode: "readwrite"
        });

        if (permission === "granted") {
            return true;
        }

        permission = await handle.requestPermission({
            mode: "readwrite"
        });

        return permission === "granted";
    }

    async function getOrCreateFolder(parentHandle, folderName) {

        return await parentHandle.getDirectoryHandle(
            folderName,
            { create: true }
        );
    }

    async function saveBlob(fileName, blob, folders = []) {

        const rootHandle = await loadRootHandle();

        if (!rootHandle) {
            throw new Error(
                "Root folder is not configured. Please select your root folder first."
            );
        }

        const permission =
            await ensurePermission(rootHandle);

        if (!permission) {
            throw new Error(
                "Permission to write files was denied."
            );
        }

        let currentHandle = rootHandle;

        for (const folder of folders) {

            currentHandle =
                await getOrCreateFolder(
                    currentHandle,
                    folder
                );
        }

        const fileHandle =
            await currentHandle.getFileHandle(
                fileName,
                { create: true }
            );

        const writable =
            await fileHandle.createWritable();

        await writable.write(blob);

        await writable.close();

        return true;
    }

    return {
        saveBlob
    };

})();