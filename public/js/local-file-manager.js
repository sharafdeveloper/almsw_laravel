const POSFileManager = (() => {

    const DB_NAME = "POSFileAccess";
    const STORE_NAME = "handles";
    const ROOT_KEY = "rootDir";

    /*
    |--------------------------------------------------------------------------
    | Open IndexedDB
    |--------------------------------------------------------------------------
    */

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
                reject(
                    new Error(
                        "Could not open local storage database."
                    )
                );
            };
        });
    }


    /*
    |--------------------------------------------------------------------------
    | Save Root Folder Handle
    |--------------------------------------------------------------------------
    */

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
                reject(
                    new Error(
                        "Could not save the selected root folder."
                    )
                );
            };
        });
    }


    /*
    |--------------------------------------------------------------------------
    | Load Root Folder Handle
    |--------------------------------------------------------------------------
    */

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
                reject(
                    new Error(
                        "Could not load the saved root folder."
                    )
                );
            };
        });
    }


    /*
    |--------------------------------------------------------------------------
    | Select Root Folder
    |--------------------------------------------------------------------------
    */

    async function selectRootFolder() {

        if (!("showDirectoryPicker" in window)) {

            throw new Error(
                "Your browser does not support local folder access. Please use Google Chrome or Microsoft Edge."
            );
        }

        try {

            const handle =
                await window.showDirectoryPicker({
                    mode: "readwrite"
                });

            if (!handle) {

                throw new Error(
                    "No root folder was selected."
                );
            }

            await saveHandle(handle);

            return handle;

        } catch (error) {

            /*
            | User pressed Cancel
            */

            if (error.name === "AbortError") {

                throw new Error(
                    "Folder selection was cancelled. Please select the root folder to save invoices."
                );
            }

            console.error(
                "Root Folder Selection Error:",
                error
            );

            throw new Error(
                error.message ||
                "Could not select the root folder."
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Get Root Folder Handle
    |--------------------------------------------------------------------------
    */

    async function getRootHandle() {

        if (!("showDirectoryPicker" in window)) {

            throw new Error(
                "Your browser does not support local folder access. Please use Google Chrome or Microsoft Edge."
            );
        }

        let handle = null;

        try {

            handle = await loadHandle();

        } catch (error) {

            console.error(
                "IndexedDB Load Error:",
                error
            );

            throw new Error(
                "Could not access saved folder information. Please try again."
            );
        }


        /*
        |--------------------------------------------------------------------------
        | First Time
        |--------------------------------------------------------------------------
        */

        if (!handle) {

            try {

                handle =
                    await window.showDirectoryPicker({
                        mode: "readwrite"
                    });

            } catch (error) {

                if (error.name === "AbortError") {

                    throw new Error(
                        "Folder selection was cancelled. Please select the root folder to save invoices."
                    );
                }

                console.error(
                    "Folder Picker Error:",
                    error
                );

                throw new Error(
                    error.message ||
                    "Could not select the root folder."
                );
            }


            if (!handle) {

                throw new Error(
                    "Root folder was not selected."
                );
            }


            try {

                await saveHandle(handle);

            } catch (error) {

                console.error(
                    "Save Handle Error:",
                    error
                );

                throw new Error(
                    "Folder was selected, but its permission could not be saved."
                );
            }

            return handle;
        }


        /*
        |--------------------------------------------------------------------------
        | Check Existing Permission
        |--------------------------------------------------------------------------
        */

        try {

            let permission =
                await handle.queryPermission({
                    mode: "readwrite"
                });


            /*
            | Permission already granted
            */

            if (permission === "granted") {
                return handle;
            }


            /*
            | Permission needs to be requested again
            */

            permission =
                await handle.requestPermission({
                    mode: "readwrite"
                });


            if (permission === "granted") {
                return handle;
            }


            /*
            | Permission denied
            */

            if (permission === "denied") {

                throw new Error(
                    "Permission to access the root folder was denied. Please allow folder access and try again."
                );
            }


            /*
            | Permission still not granted
            */

            throw new Error(
                "Root folder permission was not granted. Please allow access to save the invoice."
            );

        } catch (error) {

            console.error(
                "Root Folder Permission Error:",
                error
            );

            /*
            | Keep our own useful error message
            */

            if (
                error.message &&
                (
                    error.message.includes("Permission") ||
                    error.message.includes("permission")
                )
            ) {
                throw error;
            }

            throw new Error(
                "Could not access the root folder. Please select the folder again."
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Save PDF Blob
    |--------------------------------------------------------------------------
    */

    async function saveBlob(filename, blob, folders = []) {

        const rootHandle = await getRootHandle();

        let currentHandle = rootHandle;

        /*
         * Today's date folder.
         *
         * Root:
         * C:\Users\CC\Documents\date
         *
         * Today:
         * 11-08-2026
         */
        const now = new Date();

        const day =
            String(now.getDate()).padStart(2, "0");

        const month =
            String(now.getMonth() + 1).padStart(2, "0");

        const year =
            now.getFullYear();

        const dateFolder =
            `${day}-${month}-${year}`;

        /*
         * Open today's existing date folder.
         *
         * IMPORTANT:
         * create:false means we will NOT create
         * the date folder automatically.
         */
        try {

            currentHandle =
                await currentHandle.getDirectoryHandle(
                    dateFolder,
                    {
                        create: false
                    }
                );

        } catch (error) {

            throw new Error(
                `Today's date folder "${dateFolder}" was not found inside the selected date folder. Please create it first.`
            );
        }

        /*
         * Create/open the required record folders.
         *
         * Example:
         *
         * Purchase Invoice
         *      └── Ali
         *
         * OR
         *
         * sale Invoice
         *      └── Ali
         *
         * OR
         *
         * Cashbook
         */
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

        /*
         * Create / overwrite PDF file.
         */
        const fileHandle =
            await currentHandle.getFileHandle(
                filename,
                {
                    create: true
                }
            );

        const writable =
            await fileHandle.createWritable();

        try {

            await writable.write(blob);

        } finally {

            await writable.close();
        }

        return true;
    }


    /*
    |--------------------------------------------------------------------------
    | Public API
    |--------------------------------------------------------------------------
    */

    return {

        saveBlob,
        selectRootFolder,
        getRootHandle

    };


})();