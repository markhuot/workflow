import React, { useCallback } from 'react';
import { Handle, Position } from 'reactflow';
import {WorkflowNode} from "../../ts/WorkflowNode";
import {WorkflowInput} from "../../ts/WorkflowInput";


export function Html({ data }) {
    return (
        <WorkflowNode label={data.label} type="fetchers">
            <WorkflowInput label="URL" placeholder="http://www.example.com" />
            <WorkflowInput label="Selector" placeholder=".item" />
        </WorkflowNode>
    );
}
