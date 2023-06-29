import React, { useState } from 'react';
import ImageMarker from 'react-image-marker';
import building from '../../../images/floorPlan.png';

function ImageMarkerList({ labImage, deviceCoordsList, height }) {
  const [markers, setMarkers] = useState(deviceCoordsList);

  function CustomMarker() {
    return (
      <img
        alt="Pointer"
        width="20"
        height="20"
        src={require('../../../images/deviceIcons/dataloger.png')}
        srcSet={require('../../../images/deviceIcons/dataloger.png')}
      />
    );
  }
  return (
    <div className='mt-24 sm:mt-0'>
    <ImageMarker
      src={labImage || building}
      markers={deviceCoordsList}
      onAddMarker={(marker) => { setFloorCoordinations(marker); setMarkers([...markers, marker]); }}
      markerComponent={CustomMarker}
      extraClass={height || "h-[55vh]"}
    />
  </div>

  );
}

export default ImageMarkerList;
