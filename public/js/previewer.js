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

function getFrame() {
    return document.querySelector("#collabora-editor__dialog > .collabora-frame__preview");
}

function previewField(coolUrl) {
    let iframe = getFrame();
    iframe.src = coolUrl;
    document.querySelector("#collabora-editor__dialog").show();
}

function closePreview() {
    let iframe = getFrame();
    iframe.src = "about:blank";
    document.querySelector('#collabora-editor__dialog').close();
}

(function () {
    // This is meant to receive the message from the frame.php iframe.
    function receiveMessage(event) {
        let origin = new URL(getFrame().src).origin;
        if (!event || event.origin != origin) {
            return;
        }
        let msg;
        try {
            msg = JSON.parse(event.data);
            if (!msg) {
                return;
            }
        } catch (error) {
            console.error(error);
            return;
        }

        switch (msg.MessageId) {
        case "UI_Close":
            closePreview();
            break;
        }
    }

    window.addEventListener("message", receiveMessage, false);
})()
