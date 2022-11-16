import React, { useCallback } from 'react';
import { Handle, Position } from 'reactflow';
import {WorkflowNode} from "../../ts/WorkflowNode";
import {WorkflowInput} from "../../ts/WorkflowInput";


export function Map({ data }) {
    return (
        <WorkflowNode label={data.label} type="mutations">
            <WorkflowInput label="Map" type="textarea" placeholder="http://www.example.com" />
        </WorkflowNode>
    );
}
