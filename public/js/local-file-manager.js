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

    async function saveBlob(
        filename,
        blob,
        folders = []
    ) {

        if (!filename) {

            throw new Error(
                "Invoice filename is missing."
            );
        }


        if (!blob) {

            throw new Error(
                "PDF data is empty. The invoice could not be saved."
            );
        }


        if (!(blob instanceof Blob)) {

            throw new Error(
                "Invalid PDF data received."
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Get Root Folder
        |--------------------------------------------------------------------------
        */

        const rootHandle =
            await getRootHandle();


        let currentHandle =
            rootHandle;


        /*
        |--------------------------------------------------------------------------
        | Create / Open Folders
        |--------------------------------------------------------------------------
        */

        for (const folderName of folders) {

            if (!folderName) {
                continue;
            }


            try {

                currentHandle =
                    await currentHandle.getDirectoryHandle(
                        folderName,
                        {
                            create: true
                        }
                    );

            } catch (error) {

                console.error(
                    "Folder Creation Error:",
                    error
                );

                throw new Error(
                    `Could not create or access folder "${folderName}".`
                );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Create / Open PDF File
        |--------------------------------------------------------------------------
        */

        let fileHandle;

        try {

            fileHandle =
                await currentHandle.getFileHandle(
                    filename,
                    {
                        create: true
                    }
                );

        } catch (error) {

            console.error(
                "File Creation Error:",
                error
            );

            throw new Error(
                `Could not create invoice file "${filename}".`
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Write PDF
        |--------------------------------------------------------------------------
        */

        let writable = null;

        try {

            writable =
                await fileHandle.createWritable();

            await writable.write(blob);

            await writable.close();

            writable = null;

        } catch (error) {

            console.error(
                "PDF Write Error:",
                error
            );


            /*
            | Try to close writable stream if something failed
            */

            if (writable) {

                try {
                    await writable.abort();
                } catch (abortError) {
                    console.error(
                        "Writable Abort Error:",
                        abortError
                    );
                }
            }


            throw new Error(
                `Could not save invoice "${filename}". Please check folder permission and available storage.`
            );
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