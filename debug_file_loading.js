// Add this to browser console to debug
console.log('=== Debugging File Loading ===');
const projectId = 11;
const folderId = null;

console.log(`Testing: /projects/${projectId}/files?folder=${folderId ? folderId : ''}`);

fetch(`/projects/${projectId}/files?folder=${folderId ? folderId : ''}`)
    .then(response => {
        console.log('Response:', response);
        console.log('Status:', response.status, response.statusText);
        console.log('Headers:', [...response.headers.entries()]);

        return response.text().then(text => {
            console.log('Response text:', text);
            try {
                const json = JSON.parse(text);
                console.log('Parsed JSON:', json);
                return json;
            } catch(e) {
                console.error('Failed to parse JSON:', e);
                return null;
            }
        });
    })
    .then(data => {
        console.log('Files data:', data);
    })
    .catch(error => {
        console.error('Error:', error);
    });

