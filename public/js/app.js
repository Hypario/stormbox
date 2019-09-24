const chunkSize = 64 * 1024;

const div = document.getElementById('drop');
// triggered when a file is hovering the div
div.addEventListener('dragover', (e) => {
  e.preventDefault();
});

/// triggered when an item is droped in the div
div.addEventListener('drop', (e) => {
  e.preventDefault();
  // for each items dropped, traverse the file tree
  for (const item of e.dataTransfer.items) {
    traverseFileTree(item.webkitGetAsEntry())
  }
});

function traverseFileTree(item, path = "") {
  if (item.isFile) {
    // get file
    item.file(file => {
      // transfer it
      send(file, chunkSize);
    });
  } else if (item.isDirectory) {
    // if directory, traverse the file tree again
    const dirReader = item.createReader();
    dirReader.readEntries((entries) => {
      for (let i = 0; i < entries.length; i++) {
        traverseFileTree(entries[i], path + item.name + "/");
      }
    });
  }
}

// send the file in part
function send(file, length = 64 * 1024, offset = 0) {
  const fileSize = file.size === 0 ? 1 : file.size;
  const nbChunk = Math.ceil(fileSize / length);

  // actual chunk we are manipulating
  const chunk = (offset / length) + 1;

  // get the path of the file or directory
  const path = file.webkitRelativePath !== "" ? file.webkitRelativePath : file.name;

  const data = new FormData();
  // put everything in FormData type
  data.append('blob', file.slice(offset, length + offset));
  data.append('path', path);
  data.append('nbChunk', chunkSize);
  data.append('chunk', chunk);
  data.append('chunkSize', length);

  // send the data to the server
  sendChunk(data).then(response => {
    // if the upload is a success and we have still chunks to do
    if (response["Error"] === 0 && chunk < nbChunk) {
      offset += length;
      // send another chunk
      send(file, length, offset);
    }
  })
}

// send the data to the server
async function sendChunk(data) {

  const response = await fetch('/api/upload', {
    method: 'POST',
    body: data,
  });

  return response.json();
}

// get the file using the data given
async function getFile(data) {
  const response = await fetch('/api/files', {
    method: 'POST',
    body: data
  });

  return response.json();
}

// add an even to the form who is used to get the files
let form = document.getElementById("getFile");
form.addEventListener('submit', function (e) {
  e.preventDefault();

  let data = new FormData(this);

  getFile(data).then(response => {
      console.log(response);
    }
  )
});
