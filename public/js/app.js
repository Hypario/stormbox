const chunkSize = 4 * 10 ** 6; // size of a chunked file in MB
const percent = document.getElementById("percent"); // where to show the percentage
const dropForm = document.getElementById('drop');
const dropInput = document.getElementById('fileinput');

// triggered when a file is hovering the div
dropForm.addEventListener('dragover', (e) => {
  e.preventDefault();
});

/// triggered when an item is droped in the div
dropForm.addEventListener('drop', (e) => {
  e.preventDefault();
  // for each items dropped, traverse the file tree
  for (const item of e.dataTransfer.items) {
    traverseFileTree(item.webkitGetAsEntry())
  }
});

dropInput.addEventListener('change', (e) => {
  console.log(e);
});

// traverse the file tree
function traverseFileTree(item) {
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
        traverseFileTree(entries[i]);
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

  let percentage = Math.round((chunk / nbChunk) * 100);
  percent.innerText = percentage + "%";

  // get the path of the file or directory
  const path = file.webkitRelativePath !== "" ? file.webkitRelativePath : file.name;

  const data = new FormData();

  // put everything in FormData type
  data.append('path', path);
  data.append('nbChunk', nbChunk);
  data.append('chunk', chunk);
  data.append('blob', file.slice(offset, length + offset));

  // send the data to the server
  sendChunk(data).then(response => {
    // if the upload is a success and we have still chunks to do
    if (response.status === 204 && chunk < nbChunk) {
      offset += length;
      // send another chunk
      send(file, length, offset);
    }
  })
}

// send the data to the server
async function sendChunk(data) {

  return await fetch('/api/upload', {
    method: 'POST',
    body: data,
  });
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
const getFileForm = document.getElementById("getFile");
getFileForm.addEventListener('submit', function (e) {
  e.preventDefault();

  let data = new FormData(this);

  getFile(data).then(response => {
      console.log(response);
    }
  )
});
