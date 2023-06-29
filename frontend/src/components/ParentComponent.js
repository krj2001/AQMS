import React, { useState } from 'react';
import Dashboard from '../components/DashboardComponent';
import Navbar from './navbarComponent/Navbar';


function ParentComponent() {
  const [date, setDate] = useState(null);

  return (
    <div>
      <Navbar setDate={setDate} />
      <Dashboard date={date} />
    </div>
  );
}

export default ParentComponent;
