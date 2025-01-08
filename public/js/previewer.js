/* -*- js-indent-level: 4 -*- */
/*
 * Copyright the Collabora Online contributors.
 *
 * SPDX-License-Identifier: MPL-2.0
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

function previewField(coolUrl) {
    let iframe = document.querySelector("#collabora-editor__dialog > .collabora-frame__preview");
    iframe.src = coolUrl;
    document.querySelector("#collabora-editor__dialog").show();
}

function closePreview() {
    let iframe = document.querySelector("#collabora-editor__dialog > .collabora-frame__preview");
    iframe.src = "about:blank";
    document.querySelector('#collabora-editor__dialog').close();
}

(function () {
    function receiveMessage(event) {
        let msg = JSON.parse(event.data);
        if (!msg) {
            return;
        }

        switch (msg.MessageId) {
        case "App_LoadingStatus":
            if (msg.Values && msg.Values.Status == "Document_Loaded") {
                postReady();
            }
            break;
        case "UI_Close":
            closePreview();
            break;
        }
    }

    window.addEventListener("message", receiveMessage, false);
})()
