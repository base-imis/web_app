<!-- Last Modified Date: 19-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)  (© ISPL, 2024) -->
<div class="panel-group m-3" id="accordion" role="tablist" aria-multiselectable="true">
        <div class="panel panel-default">
                    <div class="card">
                        <div class="card-header" id="heading">
                            <h5 class="mb-0">
                                <a class="btn btn-link" data-toggle="collapse" data-target="#collapseparam" aria-expanded="true" aria-controls="collapse">
                                Equity
                                </a>
                            </h5>
                        </div>
                        <div id="collapseparam" class="collapse show" aria-labelledby="heading" data-parent="#accordion">
                            <div class="card-body">
                                <table class="table table-bordered" width='100%'>
                                    <tr class="" width='100%'>
                                        <th width='50%'>Indicators</th>
                                        <th width='10%'>Outcome</th>
                                        <th width='20%'>Value</th>
                                    </tr>
                                    <tr>
                                        <td>% of LIC population with access to safe individual toilets</td>
                                        <td>equity</td>
                                        <td><input type="text" class="form-control data-input" placeholder="Enter value in percent" min="0" max="100" step="1" name="EQ-1" value="{{ $data['EQ-1'] ?? '' }}" {{ $data['EQ-1'] !== null ? 'disabled' : '' }}>
                                        <input type="hidden" name="EQ-1_hidden" value="{{ isset($data['EQ-1']) ? $data['EQ-1'] : '' }}"></td>
                                    </tr>
                                                                        
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header" id="headingOne">
                            <h5 class="mb-0">
                                <a class="btn btn-link" data-toggle="collapse" data-target="#collapseparam1" aria-expanded="true" aria-controls="collapseOne">
                                   Safety
                                </a>
                            </h5>
                        </div>
                        <div id="collapseparam1" class="collapse show" aria-labelledby="headingOne" data-parent="#accordion">
                            <div class="card-body">
                                <table class="table table-bordered" width='100%'>
                                    <tr width='100%'>
                                        <th width='50%'>Indicators</th>
                                        <th width='10%'>Outcome</th>
                                        <th width='20%'>Value</th>
                                    </tr>
                                    <tr>
                                        <td>Population with access to safe individual toilets</td>
                                        <td>safety</td>
                                        <td><input type="text" class="form-control data-input" placeholder="Enter value in percent" min="0" max="100" step="1" name="SF-1a"  value="{{ $data['SF-1a'] ?? '' }}" {{ $data['SF-1a'] !== null ? 'disabled' : '' }}>
                                        <input type="hidden" name="SF-1a_hidden" value="{{ isset($data['SF-1a']) ? $data['SF-1a'] : '' }}"></td>
                                    </tr>
                                    <tr>
                                        <td>IHHL OSSs that have been desludged</td> 
                                        <td>safety</td>
                                        <td><input type="text" class="form-control data-input" placeholder="Enter value in percent" min="0" max="100" step="1" name="SF-1b"  value="{{ $data['SF-1b'] ?? '' }}" {{ $data['SF-1b'] !== null ? 'disabled' : '' }}>
                                        <input type="hidden" name="SF-1b_hidden" value="{{ isset($data['SF-1b']) ? $data['SF-1b'] : '' }}">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Collected FS disposed at treatment plant or designated disposal site</td>
                                        <td>safety</td>
                                        <td><input type="text" class="form-control data-input" placeholder="Enter value in percent" min="0" max="100" step="1" name="SF-1c" value="{{ $data['SF-1c'] ?? '' }}" {{ $data['SF-1c'] !== null ? 'disabled' : '' }}>
                                        <input type="hidden" name="SF-1c_hidden" value="{{ isset($data['SF-1c']) ? $data['SF-1c'] : '' }}"></td>
                                    </tr>
                                    <tr>
                                        <td>FS treatment capacity as a % of total FS generated from non-sewered connections</td>
                                        <td>safety</td>
                                        <td><input type="text" class="form-control data-input" placeholder="Enter value in percent" min="0" max="100" step="1" name="SF-1d" value="{{ $data['SF-1d'] ?? '' }}" {{ $data['SF-1d'] !== null ? 'disabled' : '' }}>
                                        <input type="hidden" name="SF-1d_hidden" value="{{ isset($data['SF-1d']) ? $data['SF-1d'] : '' }}"></td>
                                    </tr>
                                    <tr>
                                        <td>FS treatment capacity as a % of volume disposed at the treatment plant</td>
                                        <td>safety</td>
                                        <td><input type="text" class="form-control data-input" placeholder="Enter value in percent" min="0" max="100" step="1" name="SF-1e" value="{{ $data['SF-1e'] ?? '' }}" {{ $data['SF-1e'] !== null ? 'disabled' : ''  }}>
                                        <input type="hidden" name="SF-1e_hidden" value="{{ isset($data['SF-1e']) ? $data['SF-1e'] : '' }}"></td>
                                    </tr>
                                    <tr>
                                        <td>WW treatment capacity as a % of total WW generated from sewered connections and greywater and supernatant generated from non-sewered connections</td>
                                        <td>safety</td>
                                        <td><input type="text" class="form-control data-input" placeholder="Enter value in percent" min="0" max="100" step="1" name="SF-1f" value="{{ $data['SF-1f'] ?? '' }}" {{ $data['SF-1f'] !== null ? 'disabled' : ''  }}>
                                        <input type="hidden" name="SF-1f_hidden" value="{{ isset($data['SF-1f']) ? $data['SF-1f'] : '' }}"></td>
                                    </tr>
                                    <tr>
                                        <td>Effectiveness of FS treatment in meeting prescribed standards for effluent discharge and biosolids disposal</td>
                                        <td>safety</td>
                                        <td><input type="text" class="form-control data-input" placeholder="Enter value in percent" min="0" max="100" step="1" name="SF-1g" value="{{ $data['SF-1g'] ?? ''}}" {{ $data['SF-1g'] !== null ? 'disabled' : ''  }}></td>
                                        <input type="hidden" name="SF-1g_hidden" value="{{ isset($data['SF-1g']) ? $data['SF-1g'] : '' }}">
                                    </tr>
                                    <tr>
                                        <td>Low income community (LIC) population with access to safe individual toilets</td>
                                        <td>safety</td>
                                        <td><input type="text" class="form-control data-input" placeholder="Enter value in percent" min="0" max="100" step="1" name="SF-2a" value="{{ $data['SF-2a'] ?? '' }}" {{ $data['SF-2a'] !== null ? 'disabled' : ''  }}></td>
                                        <input type="hidden" name="SF-2a_hidden" value="{{ isset($data['SF-2a']) ? $data['SF-2a'] : '' }}">
                                    </tr>
                                    <tr>
                                        <td>LIC OSSs that have been desludged</td>
                                        <td>safety</td>
                                        <td><input type="text" class="form-control data-input" placeholder="Enter value in percent" min="0" max="100" step="1" name="SF-2b" value="{{ $data['SF-2b'] ?? '' }}" {{ $data['SF-2b'] !== null ? 'disabled' : ''  }}></td>
                                        <input type="hidden" name="SF-2b_hidden" value="{{ isset($data['SF-2b'])  ? $data['SF-2b'] : ''}}">
                                    </tr>
                                    <tr>
                                        <td>FS collected from LIC that is disposed at treatment plant or designated disposal site</td>
                                        <td>safety</td>
                                        <td><input type="text" class="form-control data-input" placeholder="Enter value in percent" min="0" max="100" step="1" name="SF-2c" value="{{ $data['SF-2c'] ?? '' }}" {{ $data['SF-2c'] !== null ? 'disabled' : ''  }}></td>
                                        <input type="hidden" name="SF-2c_hidden" value="{{ isset($data['SF-2c']) ? $data['SF-2c'] : '' }}">
                                    </tr>
                                    <tr>
                                        <td>Shared facilities that adhere to principles of universal design</td>
                                        <td>safety</td>
                                        <td><input type="text" class="form-control data-input" placeholder="Enter value in percent" min="0" max="100" step="1" name="SF-3b" value="{{ $data['SF-3b'] ?? '' }}" {{ $data['SF-3b'] !== null ? 'disabled' : ''  }}>
                                        <input type="hidden" name="SF-3b_hidden" value="{{ isset($data['SF-3b']) ? $data['SF-3b'] : '' }}"></td>
                                    </tr>
                                    <tr>
                                        <td>Dependent population (without IHHL) with access to safe shared facilities</td>
                                        <td>safety</td>
                                        <td><input type="text" class="form-control data-input" placeholder="Enter value in percent" min="0" max="100" step="1" name="SF-3a" value="{{ $data['SF-3a'] ?? '' }}" {{ $data['SF-3a'] !== null ? 'disabled' : ''  }}>
                                        <input type="hidden" name="SF-3a_hidden" value="{{ isset($data['SF-3a']) ? $data['SF-3a'] : '' }}"></td>
                                    </tr>
                                    <tr>
                                        <td>Shared facility users who are women</td>
                                        <td>safety</td>
                                        <td><input type="text" class="form-control data-input" placeholder="Enter value in percent" min="0" max="100" step="1" name="SF-3c" value="{{ $data['SF-3c'] ?? '' }}" {{ $data['SF-3c'] !== null ? 'disabled' : ''  }}>
                                        <input type="hidden" name="SF-3c_hidden" value="{{ isset($data['SF-3c']) ? $data['SF-3c'] : '' }}"></td>
                                    </tr>
                                    <tr>
                                        <td>Average distance from HH to shared facility (m)</td>
                                        <td>safety</td>
                                        <td><input type="text" class="form-control data-input" placeholder="Enter value in percent" min="0" max="100" step="1" name="SF-3e" value="{{ $data['SF-3e'] ?? '' }}" {{ $data['SF-3e'] !== null ? 'disabled' : ''  }}>
                                        <input type="hidden" name="SF-3e_hidden" value="{{ isset($data['SF-3e']) ? $data['SF-3e'] : '' }}"></td>
                                    </tr>
                                    <tr>
                                        <td>PT where FS generated is safely transported to TP or safely disposed</td>
                                        <td>safety</td>
                                        <td><input type="text" class="form-control data-input" placeholder="Enter value in percent" min="0" max="100" step="1" name="SF-4a" value="{{ $data['SF-4a'] ?? '' }}" {{ $data['SF-4a'] !== null ? 'disabled' : ''  }}>
                                        <input type="hidden" name="SF-4a_hidden" value="{{ isset($data['SF-4a']) ? $data['SF-4a'] : '' }}"></td>
                                    </tr>
                                    <tr>
                                        <td>PT that adhere to principles of universal design</td>
                                        <td>safety</td>
                                        <td><input type="text" class="form-control data-input" placeholder="Enter value in percent" min="0" max="100" step="1" name="SF-4b" value="{{ $data['SF-4b'] ?? '' }}" {{ $data['SF-4b'] !== null ? 'disabled' : ''  }}>
                                        <input type="hidden" name="SF-4b_hidden" value="{{ isset($data['SF-4b']) ? $data['SF-4b'] : '' }}"></td>
                                    </tr>
                                    <tr>
                                        <td>PT users who are women</td>
                                        <td>safety</td>
                                        <td><input type="text" class="form-control data-input" placeholder="Enter value in percent" min="0" max="100" step="1" name="SF-4d" value="{{ $data['SF-4d'] ?? '' }}" {{ $data['SF-4d'] !== null ? 'disabled' : ''  }}>
                                        <input type="hidden" name="SF-4d_hidden" value="{{ isset($data['SF-4d']) ? $data['SF-4d'] : '' }}"></td>
                                    </tr>
                                    <tr>
                                        <td>Educational institutions where FS generated is safely transported to TP</td>
                                        <td>safety</td>
                                        <td><input type="text" class="form-control data-input" placeholder="Enter value in percent" min="0" max="100" step="1" name="SF-5" value="{{ $data['SF-5'] ?? '' }}" {{ $data['SF-5'] !== null ? 'disabled' : ''  }}>
                                        <input type="hidden" name="SF-5_hidden" value="{{ isset($data['SF-5']) ? $data['SF-5'] : '' }}"></td>
                                    </tr>
                                    <tr>
                                        <td>Healthcare facilities where FS generated is safely transported to TP or safely disposed in situ</td>
                                        <td>safety</td>
                                        <td><input type="text" class="form-control data-input" placeholder="Enter value in percent" min="0" max="100" step="1" name="SF-6" value="{{ $data['SF-6'] ?? '' }}" {{ $data['SF-6'] !== null ? 'disabled' : ''  }}>
                                        <input type="hidden" name="SF-6_hidden" value="{{ isset($data['SF-6']) ? $data['SF-6'] : '' }}"></td>
                                    </tr>
                                    <tr>
                                        <td>Desludging services completed mechanically or semi-mechanically</td>
                                        <td>safety</td>
                                        <td><input type="text" class="form-control data-input" placeholder="Enter value in percent" min="0" max="100" step="1" name="SF-7" value="{{ $data['SF-7'] ?? '' }}" {{ $data['SF-7'] !== null ? 'disabled' : ''  }}>
                                        <input type="hidden" name="SF-7_hidden" value="{{ isset($data['SF-7']) ? $data['SF-7'] : '' }}"></td>
                                    </tr>
                                    <tr>
                                        <td> % of water contamination compliance (on fecal coliform)</td>
                                        <td>safety</td>
                                        <td><input type="text" class="form-control data-input" placeholder="Enter value in percent" min="0" max="100" step="1" name="SF-9" value="{{ $data['SF-9'] ?? '' }}" {{ $data['SF-7'] !== null ? 'disabled' : ''  }}>
                                        <input type="hidden" name="SF-7_hidden" value="{{ isset($data['SF-9']) ? $data['SF-9'] : '' }}"></td>
                                    </tr>
                                   
                                </table>
                            </div>
                        </div>
                    </div>

                      
             
        </div>

    <!-- Static Button Logic -->
    <div class="footer">
        {!! Form::hidden('year', $year) !!}
        {!! Form::submit('Save', ['class' => 'btn btn-info']) !!}
    </div>
</div>
