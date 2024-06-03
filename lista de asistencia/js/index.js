document.getElementById('uploadForm').addEventListener('submit', function(event) {
    event.preventDefault();
    const fileInput = document.getElementById('fileInput');
    const file = fileInput.files[0];
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
            const data = new Uint8Array(event.target.result);
            const workbook = XLSX.read(data, {type: 'array'});
            
            
            const firstSheetName = workbook.SheetNames[0];
            const worksheet = workbook.Sheets[firstSheetName];
            const sheetData = XLSX.utils.sheet_to_json(worksheet, {header: 1});

            fetch('index.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(sheetData)
            })
            .then(response => response.text()) 
            .then(text => {
                try {
                    const data = JSON.parse(text); 
                    document.getElementById('result').innerHTML = `
                        <h3>Resultado</h3>
                        <pre>${JSON.stringify(data, null, 2)}</pre>
                    `;
                } catch (error) {
                    document.getElementById('result').innerHTML = `
                        <h3>Error</h3>
                        <pre>Respuesta no es JSON válida: ${text}</pre>
                    `;
                }
            })
            .catch(error => {
                document.getElementById('result').innerHTML = `
                    <h3>Error</h3>
                    <pre>${error.message}</pre>
                `;
            });
        };
        reader.readAsArrayBuffer(file);
    } else {
        alert('Por favor selecciona un archivo primero.');
    }
});
