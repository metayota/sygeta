class SignaturePadTag extends Tag {

    render() {

        let signaturePadLoaded = Tag.load('/node_modules/signature_pad/dist/signature_pad.umd.min.js');

        signaturePadLoaded.then(result => {

            const canvas = this.signatureCanvas;

            // === Schritt 1: Canvas richtig skalieren ===
            const resizeCanvas = () => {
                const rect = canvas.getBoundingClientRect();
                const ratio = Math.max(window.devicePixelRatio || 1, 1);

                canvas.width = rect.width * ratio;
                canvas.height = rect.height * ratio;

                const ctx = canvas.getContext("2d");
                ctx.setTransform(1, 0, 0, 1, 0, 0);
                ctx.scale(ratio, ratio);
            };

            // Kleines Delay, weil dein Framework render() sehr früh aufruft
            setTimeout(() => {

                resizeCanvas();   // Canvas interne Größe setzen

                // === Schritt 2: SignaturePad jetzt erst initialisieren ===
                const signaturePad = new SignaturePad(canvas,{
                    minWidth: 0.5,             // z. B. etwas breitere Mindestlinie
                    maxWidth: 1.8,             // etwas breitere Maxlinie
                    throttle: 16,              // Zeichen-Punkte alle 16 ms
                    minDistance: 5,            // nur bei >5 px Bewegung
                    velocityFilterWeight: 1  // verringerter Einfluss der Geschwindigkeit
                });

                // Wenn du Fenster-Resize brauchst: aktivierbar
                window.addEventListener("resize", () => {
                    const data = signaturePad.toData();
                    resizeCanvas();
                    signaturePad.fromData(data);
                });
                this.signaturePad = signaturePad;

                // Optional: Events aktivieren
                /*
                this.clearButton.onclick = () => signaturePad.clear();
                this.saveButton.onclick = () => {
                    if (!signaturePad.isEmpty()) {
                        const dataURL = signaturePad.toDataURL();
                        this.outputImage.src = dataURL;
                    }
                };
                */

            }, 30); // 30ms reichen, dein Framework ist schnell

        });
    }

    signDocument() {
        const dataURL = this.signaturePad.toDataURL();
        resource.action('upload_signature', {signature_image:dataURL}).then(result=> {
            toast$.display(result)
            this.fire('signed', result.filename)
        })
    }

    resetSignature() {
        this.signaturePad.clear();
    }
}
